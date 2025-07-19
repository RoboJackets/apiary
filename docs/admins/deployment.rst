:og:description: Apiary is a Laravel app packed into a Docker container deployed with HashiCorp Nomad and Consul. This section describes how to deploy a new production-grade instance of Apiary from scratch.

.. vale write-good.E-Prime = NO
.. vale Google.Passive = NO
.. vale write-good.Passive = NO

Deployment
==========

Apiary is a `Laravel <https://laravel.com/>`_ app packed into a `Docker <https://www.docker.com/>`_ container deployed with HashiCorp `Nomad <https://www.nomadproject.io/>`_ and `Consul <https://www.consul.io/>`_.
This section describes how to deploy a new production-grade instance of Apiary from scratch.

Server setup
------------

Initialize a Red Hat Enterprise Linux server using the `web-app-platform <https://github.com/RoboJackets/web-app-platform>`_ Ansible playbook.

In particular:

.. vale Google.Acronyms = NO
.. vale Google.Parens = NO

- The server must be Internet-accessible for inbound webhooks from vendor services to work, including Square, DocuSign, and Postmark
- The server must have a valid :abbr:`TLS (Transport Layer Security)` certificate
- MySQL must be installed

.. vale Google.Acronyms = YES
.. vale Google.Parens = YES

Build the app
-------------

The entire build process is encapsulated into the Dockerfile at the root of the repository.
You can build a production image using the following command.

.. code:: shell

   docker build --secret id=composer_auth,src=auth.json .

Note that you must provide an ``auth.json`` file for Composer to authenticate to the Laravel Nova repository.
See the `Laravel Nova installation instructions <https://nova.laravel.com/docs/installation.html>`_ for more details.

You should also tag the image so that it can be pushed to a registry.

Push the image to a registry
----------------------------

.. important::
   While this repository itself is open source, this project uses **confidential and proprietary** components which are packed into Docker images produced by this process.
   Images should **never** be pushed to a public registry.

.. vale Google.Acronyms = NO

The Ansible playbook includes a ``registry`` role to host a private `CNCF Distribution Registry <https://distribution.github.io/distribution/>`_ instance for storing images.
The steps are similar for any other private registry.

.. vale Google.Acronyms = YES

.. code:: shell

   docker login registry.example.robojackets.net

   docker push registry.example.robojackets.net/apiary

Note the manifest digest printed at the end of the push.

.. Vale doesn't like Consul being capitalized here
.. vale Google.Headings = NO

Add configuration to Consul
---------------------------

.. vale Google.Headings = YES
.. vale write-good.Weasel = NO

All environment-specific configuration options are stored in the Consul Key/Value Store and retrieved by Nomad when starting containers.
Apiary requires several keys to be configured.

Hostname
~~~~~~~~

The ``nginx/hostnames`` key must be a JSON map with a key of the Nomad job name and a value of the fully qualified domain name of the Apiary instance.

For example, if the Nomad job name is ``apiary-production`` and you're using ``apiary.robojackets.net`` as the domain name, the key should look like this.

.. code:: yaml

   {
     "apiary-production": "apiary.robojackets.net",
     # other key-value pairs not shown
   }

Among other uses, this mapping is used to serve 503 error pages in the event the app isn't available.

App configuration
~~~~~~~~~~~~~~~~~

App-level configuration can be split across two keys, as needed. ``apiary/shared`` is loaded for all environments, and ``apiary/<environment>`` can be used for environment-specific configuration.

The format for both keys is the same: a JSON map of key-value pairs.
The maps are transformed into environment variables, which are then read into the app configuration cache.

For a comprehensive list of options, see the ``/config/`` directory in the root of the repository.

Create a database and user
--------------------------

Apiary relies on a MySQL database for its primary data store.

You must log in to the database server as ``root`` or another administrative user, then run the commands below to initialize an empty database.

.. code:: sql

   create user apiary_example@localhost identified by 'supersecretpassword';

   create database apiary_example;

   grant all privileges on apiary_example.* to apiary_example@localhost;

The selected database name, user name, and password must be loaded in the environment-specific configuration key in Consul.

.. vale Google.WordList = NO

No other setup is required for the database.
Tables and other necessary data are initialized when the app is deployed.

.. vale Google.WordList = YES

.. Vale doesn't like Nomad being capitalized here
.. vale Google.Headings = NO

Submit the Nomad job
--------------------

.. vale Google.Headings = YES

Apiary uses Nomad as a lightweight orchestrator for Docker containers.
You must install Nomad on your machine to submit the job - see the `Nomad installation instructions <https://developer.hashicorp.com/nomad/install>`_ for more details.

Before submitting the job to Nomad, ensure that the job name is unique and includes the environment name.
The job name `can't be modified at job submit time <https://github.com/hashicorp/nomad/issues/9522>`_, so it must be done outside of the Nomad tooling.
Also ensure the region and data center match the Ansible inventory.

.. code:: shell

   export NOMAD_ADDR=https://nomad.example.robojackets.net
   # use a bootstrap token or secret id from `nomad login`
   export NOMAD_TOKEN=00000000-0000-0000-0000-000000000000

   nomad run \
     -var=image=registry.example.robojackets.net/apiary@<manifest digest from docker push>
     -var=run_background_containers=true \
     -var=precompressed_assets=true \
     -var=web_shutdown_delay=30s \
     apiary.nomad

See the jobspec file for variable descriptions.
