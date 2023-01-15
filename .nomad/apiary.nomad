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

variable "precompressed_assets" {
  type = bool
  description = "Whether assets in the image are pre-compressed"
}

variable "environment_name" {
  type = string
  description = "The name of the environment being deployed"
}

locals {
  # compressed in this context refers to the config string itself, not the assets
  compressed_nginx_configuration = trimspace(
    trimsuffix(
      trimspace(
        regex_replace(
          regex_replace(
            regex_replace(
              regex_replace(
                regex_replace(
                  regex_replace(
                    regex_replace(
                      regex_replace(
                        trimspace(
                          file("conf/nginx.conf")
                        ),
                        "server\\s{\\s",      # remove server keyword and opening bracket (autogenerated in nginx nomad job)
                        ""
                      ),
                      "server_name\\s\\S+;",  # remove server_name directive (autogenerated in nginx nomad job)
                      ""
                    ),
                    "root\\s\\S+;",           # remove root directive (autogenerated in nginx nomad job)
                    ""
                  ),
                  "listen\\s.+;",             # remove listen directive  (autogenerated in nginx nomad job)
                  ""
                ),
                "#.+\\n",                     # remove comments (no semantic difference)
                ""
              ),
              ";\\s+",                        # remove whitespace after semicolons (no semantic difference)
              ";"
            ),
            "{\\s+",                          # remove whitespace after opening brackets (no semantic difference)
            "{"
          ),
          "\\s+",                             # replace any occurrence of one or more whitespace characters with single space (no semantic difference)
          " "
        )
      ),
      "}"                                     # remove trailing closing bracket (autogenerated in nginx nomad job)
    )
  )

  # remove gzip_static directive if/when image does not contain compressed assets (handled at Concourse/operator level)
  compressed_nginx_configuration_without_gzip_static = regex_replace(local.compressed_nginx_configuration,"gzip_static\\s\\S+;","")
}

job "apiary" {
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

    volume "docusign" {
      type = "host"
      source = "apiary_${var.environment_name}_docusign"
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

        entrypoint = [
          "/bin/bash",
          "-xeuo",
          "pipefail",
          "-c",
          trimspace(file("scripts/prestart.sh"))
        ]
      }

      resources {
        cpu = 100
        memory = 128
        memory_max = 2048
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
          target = "/etc/php/8.1/fpm/pool.d/"
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
        cpu = 100
        memory = 256
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

      volume_mount {
        volume = "docusign"
        destination = "/app/storage/app/docusign/"
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

        tags = [
          "fastcgi"
        ]

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
          nginx-config = var.precompressed_assets ? local.compressed_nginx_configuration : local.compressed_nginx_configuration_without_gzip_static
          socket = "/var/opt/nomad/run/${NOMAD_JOB_NAME}-${NOMAD_ALLOC_ID}.sock"
          firewall-rules = jsonencode(["internet"])
          referrer-policy = "same-origin"
        }
      }

      restart {
        attempts = 1
        delay = "10s"
        interval = "1m"
        mode = "fail"
      }

      shutdown_delay = var.environment_name == "production" ? "30s" : "0s"
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
          memory = task.value == "worker" ? 512 : 128
          memory_max = 2048
        }

        volume_mount {
          volume = "run"
          destination = "/var/opt/nomad/run/"
        }

        volume_mount {
          volume = "docusign"
          destination = "/app/storage/app/docusign/"
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
          join("; ", [for task in var.run_background_containers ? ["web", "scheduler", "worker"] : ["web"] : "docker update --restart=always ${task}-${NOMAD_ALLOC_ID}"])
        ]
      }

      resources {
        cpu = 100
        memory = 128
        memory_max = 2048
      }

      lifecycle {
        hook = "poststart"
      }
    }
  }

  update {
    healthy_deadline = "5m"
    progress_deadline = "10m"
    auto_revert = true
    auto_promote = true
    canary = 1
  }
}
