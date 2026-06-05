DB_PATH="${NOMAD_TASK_DIR}"
DUMP_DIR="/data/meilisearch/dumps"

if [ -z "$${DB_PATH}" ]
then
    echo "NOMAD_TASK_DIR is empty; refusing to continue" >&2
    exit 1
fi

mkdir -p "$${DUMP_DIR}"

run_meilisearch() {
    exec /bin/meilisearch \
        --db-path "$${DB_PATH}" \
        --dump-dir "$${DUMP_DIR}" \
        --http-addr "127.0.0.1:${NOMAD_PORT_meilisearch}" \
        --env production \
        --max-indexing-memory 4Gb \
        --http-payload-size-limit 100Mb \
        --experimental-dumpless-upgrade \
        --master-key "${NOMAD_ALLOC_ID}" \
        "$@"
}

if [ -n "$(ls -A "$${DB_PATH}" 2>/dev/null)" ]
then
    run_meilisearch
fi

latest="$(ls -1t "$${DUMP_DIR}"/*.dump 2>/dev/null | head -n1 || true)"

if [ -z "$${latest}" ]
then
    run_meilisearch
fi

if ! /bin/meilisearch \
    --db-path "$${DB_PATH}" \
    --dump-dir "$${DUMP_DIR}" \
    --http-addr "127.0.0.1:${NOMAD_PORT_meilisearch}" \
    --env production \
    --max-indexing-memory 4Gb \
    --http-payload-size-limit 100Mb \
    --experimental-dumpless-upgrade \
    --master-key "${NOMAD_ALLOC_ID}" \
    --import-dump "$${latest}"
then
    rm -rf "$${DB_PATH}"/*
    run_meilisearch
fi
