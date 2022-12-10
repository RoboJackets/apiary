{{- range $key, $value := (key "apiary/shared" | parseJSON) -}}
{{- $key | trimSpace -}}={{- $value | toJSON }}
{{ end -}}
CAS_HOSTNAME="sso.gatech.edu"
CAS_REAL_HOSTS="sso.gatech.edu"
CAS_SESSION_NAME="__Host-cas_session"
PASSPORT_COOKIE_NAME="__Host-apiary_token"
CAS_LOGOUT_URL="https://sso.gatech.edu/cas/logout"
CAS_VERSION="3.0"
CAS_ENABLE_SAML="false"
{{- range service "mysql" }}
DB_SOCKET="{{- index .ServiceMeta "socket" | trimSpace -}}"
{{ end }}
REDIS_CLIENT="phpredis"
REDIS_SCHEME="unix"
{{- range service "redis" }}
REDIS_PATH="{{- index .ServiceMeta "socket" | trimSpace -}}"
{{ end }}
REDIS_PASSWORD="{{- key "redis/password" | trimSpace -}}"
{{- range service "meilisearch-v0-30" }}
MEILISEARCH_HOST="http://127.0.0.1:{{- .Port -}}"
{{ end }}
MEILISEARCH_KEY="{{- key "meilisearch/v0-30-admin-key" | trimSpace -}}"
SESSION_SECURE_COOKIE="true"
SESSION_COOKIE="__Host-apiary_session"
{{ range $key, $value := (key (printf "apiary/%s" (slice (env "NOMAD_JOB_NAME") 7)) | parseJSON) -}}
{{- $key | trimSpace -}}={{- $value | toJSON }}
{{ end -}}
APP_ENV="{{ slice (env "NOMAD_JOB_NAME") 7 }}"
APP_URL="https://{{- with (key "nginx/hostnames" | parseJSON) -}}{{- index . (env "NOMAD_JOB_NAME") -}}{{- end -}}"
CAS_CLIENT_SERVICE="https://{{- with (key "nginx/hostnames" | parseJSON) -}}{{- index . (env "NOMAD_JOB_NAME") -}}{{- end -}}"
HOME="/secrets/"
