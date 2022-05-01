variable "image" {
  type = string
  description = "The image to use for running the service"
}

variable "persist_resumes" {
  type = bool
  description = "Whether to store resumes on a host volume, or just inside the container"
}

variable "run_background_containers" {
  type = bool
  description = "Whether to start containers for horizon and scheduled tasks, or only the web task"
}

job "apiary-production" {
  region = "campus"

  datacenters = ["bcdc"]

  type = "service"

  group "apiary" {
    volume "assets" {
      type = "host"
      source = "assets"
    }

    volume "run" {
      type = "host"
      source = "run"
    }

    dynamic "volume" {
      for_each = var.persist_resumes ? ["resumes"] : []

      labels = ["resumes"]

      content {
        type = "host"
        source = "apiary_production_resumes"
      }
    }

    task "prestart" {
      driver = "docker"

      lifecycle {
        hook = "prestart"
      }

      config {
        image = var.image

        force_pull = true

        network_mode = "host"

        mount {
          type   = "bind"
          source = "local/"
          target = "/app/storage/logs/"
        }

        entrypoint = [
          "/bin/bash",
          "-xeuo",
          "pipefail",
          "-c",
          trimspace(file("scripts/prestart.sh"))
        ]
      }

      volume_mount {
        volume = "assets"
        destination = "/assets/"
      }

      volume_mount {
        volume = "run"
        destination = "/var/opt/nomad/run/"
      }

      template {
        data = trimspace(file("conf/.env.tpl"))

        destination = "/secrets/.env"
        env = true
      }

      template {
        data = <<EOF
DOCKER_IMAGE_DIGEST="${split("@", var.image)[1]}"
PERSIST_RESUMES="${var.persist_resumes}"
EOF

        destination = "/secrets/.docker_image_digest"
        env = true
      }

      template {
        data = trimspace(file("conf/.my.cnf"))

        destination = "/secrets/.my.cnf"

        change_mode = "noop"
      }
    }

    task "web" {
      driver = "docker"

      config {
        image = var.image

        force_pull = true

        network_mode = "host"

        mount {
          type   = "bind"
          source = "local/fpm/"
          target = "/etc/php/7.4/fpm/pool.d/"
        }

        mount {
          type   = "bind"
          source = "local/"
          target = "/app/storage/logs/"
        }

        mount {
          type = "tmpfs"
          target = "/run/php"
          readonly = false
          tmpfs_options {
            size = 16000
          }
        }

        entrypoint = [
          "/bin/bash",
          "-xeuo",
          "pipefail",
          "-c",
          trimspace(file("scripts/web.sh"))
        ]
      }

      resources {
        cpu = 500
        memory = 512
        memory_max = 2048
      }

      volume_mount {
        volume = "run"
        destination = "/var/opt/nomad/run/"
      }

      dynamic "volume_mount" {
        for_each = var.persist_resumes ? ["resumes"] : []

        content {
          volume = "resumes"
          destination = "/app/storage/app/resumes/"
        }
      }

      template {
        data = trimspace(file("conf/www.conf"))

        destination = "local/fpm/www.conf"
      }

      template {
        data = trimspace(file("conf/.env.tpl"))

        destination = "/secrets/.env"
        env = true
      }

      template {
        data = "DOCKER_IMAGE_DIGEST=\"${split("@", var.image)[1]}\""

        destination = "/secrets/.docker_image_digest"
        env = true
      }

      template {
        data = trimspace(file("conf/.my.cnf"))

        destination = "/secrets/.my.cnf"

        change_mode = "noop"
      }

      service {
        name = "${NOMAD_JOB_NAME}"

        check {
          name = "GET /ping"

          type = "script"

          command = "/bin/bash"

          args = [
            "-euxo",
            "pipefail",
            "-c",
            trimspace(file("scripts/healthcheck.sh"))
          ]

          interval = "5s"
          timeout = "5s"
        }

        check_restart {
          limit = 5
          grace = "20s"
        }

        meta {
          nginx-config = trimspace(trimsuffix(trimspace(regex_replace(regex_replace(regex_replace(regex_replace(regex_replace(regex_replace(regex_replace(regex_replace(trimspace(file("conf/nginx.conf")),"server\\s{\\s",""),"server_name\\s\\S+;",""),"root\\s\\S+;",""),"listen\\s.+;",""),"#.+\\n",""),";\\s+",";"),"{\\s+","{"),"\\s+"," ")),"}"))
          socket = "/var/opt/nomad/run/${NOMAD_JOB_NAME}-${NOMAD_ALLOC_ID}.sock"
        }
      }

      restart {
        attempts = 1
        delay = "10s"
        interval = "1m"
        mode = "fail"
      }

      shutdown_delay = "30s"
    }


    dynamic "task" {
      for_each = var.run_background_containers ? ["scheduler", "worker"] : []

      labels = [task.value]

      content {
        driver = "docker"

        config {
          image = var.image

          force_pull = true

          network_mode = "host"

          mount {
            type   = "bind"
            source = "local/"
            target = "/app/storage/logs/"
          }

          entrypoint = [
            "/bin/bash",
            "-xeuo",
            "pipefail",
            "-c",
            trimspace(file("scripts/${task.value}.sh"))
          ]
        }

        resources {
          cpu = 100
          memory = 512
          memory_max = 2048
        }

        volume_mount {
          volume = "run"
          destination = "/var/opt/nomad/run/"
        }

        template {
          data = trimspace(file("conf/.env.tpl"))

          destination = "/secrets/.env"
          env = true
        }

        template {
          data = "DOCKER_IMAGE_DIGEST=\"${split("@", var.image)[1]}\""

          destination = "/secrets/.docker_image_digest"
          env = true
        }

        template {
          data = trimspace(file("conf/.my.cnf"))

          destination = "/secrets/.my.cnf"

          change_mode = "noop"
        }
      }
    }

    task "set-restart-policy" {
      driver = "raw_exec"

      config {
        command = "/usr/bin/bash"
        args    = [
          "-xue",
          "-o",
          "pipefail",
          "-c",
          join("; ", concat([for task in var.run_background_containers ? ["web", "scheduler", "worker"] : ["web"] : "docker update --restart=always ${task}-${NOMAD_ALLOC_ID}"], ["sleep 60"])),
        ]
      }

      lifecycle {
        hook = "poststart"
      }
    }
  }

  # update {
  #   healthy_deadline = "5m"
  #   progress_deadline = "10m"
  #   auto_revert = true
  #   auto_promote = true
  #   canary = 1
  # }

  update {
    max_parallel = 0
  }
}
