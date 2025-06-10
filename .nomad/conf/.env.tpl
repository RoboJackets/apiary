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
{{- range service "meilisearch-v1-15" }}
MEILISEARCH_HOST="http://127.0.0.1:{{- .Port -}}"
{{ end }}
MEILISEARCH_KEY="{{- key "meilisearch/admin-key-v1.15" | trimSpace -}}"
SESSION_SECURE_COOKIE="true"
SESSION_COOKIE="__Host-apiary_session"
{{ range $key, $value := (key (printf "apiary/%s" (slice (env "NOMAD_JOB_NAME") 7)) | parseJSON) -}}
{{- $key | trimSpace -}}={{- $value | toJSON }}
{{ end -}}
APP_ENV="{{ slice (env "NOMAD_JOB_NAME") 7 }}"
APP_URL="https://{{- with (key "nginx/hostnames" | parseJSON) -}}{{- index . (env "NOMAD_JOB_NAME") -}}{{- end -}}"
CAS_CLIENT_SERVICE="https://{{- with (key "nginx/hostnames" | parseJSON) -}}{{- index . (env "NOMAD_JOB_NAME") -}}{{- end -}}"
CAS_VALIDATION="ca"
CAS_CERT="/etc/ssl/certs/USERTrust_RSA_Certification_Authority.pem"
HOME="/secrets/"
RESPONSE_CACHE_HEADER_NAME="x-cache-time"
RESPONSE_CACHE_AGE_HEADER_NAME="x-cache-age"
RESPONSE_CACHE_AGE_HEADER=true
REDIS_CLIENT="phpredis"
REDIS_SCHEME="null"
REDIS_PORT="-1"
REDIS_HOST="/alloc/tmp/redis.sock"
REDIS_PASSWORD="{{ env "NOMAD_ALLOC_ID" }}"
REDIS_DB=0
REDIS_CACHE_DB=1
