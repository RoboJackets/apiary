env -i REQUEST_METHOD=GET REQUEST_URI=/ping SCRIPT_FILENAME=/app/public/index.php cgi-fcgi -bind -connect /var/opt/nomad/run/${NOMAD_JOB_NAME}-${NOMAD_ALLOC_ID}.sock | grep -E 'pong$' || (exit 2)
