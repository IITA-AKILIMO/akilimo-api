#!/usr/bin/env bash
# =============================================================================
# Artisan aliases — sourced automatically in container bash sessions.
# Prefix conventions:
#   q*       queue operations
#   sms-*    SMS sending, delivery, backfill, archive
#   cr-*     credits
#   rpt-*    reports
#   zoho-*   Zoho sync
#   dbt-*    database triggers
#   dbv-*    database views
#   dbx-*    database extensions
# =============================================================================

APP_DIR="${APP_BASE_PATH:-/var/www/html/fuelrod}"

# ---------------------------------------------------------------------------
# Core
# ---------------------------------------------------------------------------
alias art="php ${APP_DIR}/artisan"
alias tinker="php ${APP_DIR}/artisan tinker"
alias art-list="php ${APP_DIR}/artisan list"

# ---------------------------------------------------------------------------
# Queue  (q*)
# ---------------------------------------------------------------------------
_FUELROD_QUEUES="fuelrod,sms-resend,sms-update,sms-update-chunked,archive,zoho,map-network,call-back"

alias qwork="php ${APP_DIR}/artisan queue:work"
alias qwork-all="php ${APP_DIR}/artisan queue:work redis --queue=${_FUELROD_QUEUES}"
alias qlisten="php ${APP_DIR}/artisan queue:listen"
alias qlisten-all="php ${APP_DIR}/artisan queue:listen --queue=${_FUELROD_QUEUES}"
alias qfailed="php ${APP_DIR}/artisan queue:failed"
alias qretry="php ${APP_DIR}/artisan queue:retry all"
alias qflush="php ${APP_DIR}/artisan queue:flush"
alias qclear="php ${APP_DIR}/artisan queue:clear"
qclear-all() {
    for q in default fuelrod sms-resend sms-update sms-update-chunked archive zoho map-network call-back; do
        php "${APP_DIR}/artisan" queue:clear --queue="$q"
    done
    php "${APP_DIR}/artisan" queue:prune-batches --hours=0 --cancelled=0
}
alias qrestart="php ${APP_DIR}/artisan queue:restart"
alias qmon="php ${APP_DIR}/artisan queue:monitor"
alias qbatches="php ${APP_DIR}/artisan queue:prune-batches"
alias qprune="php ${APP_DIR}/artisan queue:prune-failed"

# ---------------------------------------------------------------------------
# SMS — sending & campaigns  (sms-*)
# ---------------------------------------------------------------------------
alias sms-send="php ${APP_DIR}/artisan sms:send"
alias sms-campaign="php ${APP_DIR}/artisan campaign:dispatch"
alias sms-verify="php ${APP_DIR}/artisan campaign:verify-numbers"
alias sms-expire="php ${APP_DIR}/artisan campaign:expired"

# ---------------------------------------------------------------------------
# SMS — delivery log sync
# ---------------------------------------------------------------------------
alias sms-dlsync="php ${APP_DIR}/artisan sms:delivery-log:sync"
alias sms-dlsync-dry="php ${APP_DIR}/artisan sms:delivery-log:sync --dry-run"
alias sms-optout="php ${APP_DIR}/artisan sms:delivery-log:sync --source=outbox"
alias sms-optout-dry="php ${APP_DIR}/artisan sms:delivery-log:sync --source=outbox --dry-run"

# ---------------------------------------------------------------------------
# SMS — archive
# ---------------------------------------------------------------------------
alias sms-archive="php ${APP_DIR}/artisan sms:archive"
alias sms-archive-dry="php ${APP_DIR}/artisan sms:archive --dryrun"
alias sms-unarchive="php ${APP_DIR}/artisan sms:archive:reverse"

# ---------------------------------------------------------------------------
# SMS — backfill / updates
# ---------------------------------------------------------------------------
alias sms-reason="php ${APP_DIR}/artisan sms:update:reason"
alias sms-encoding="php ${APP_DIR}/artisan sms:update:encoding"
alias sms-count="php ${APP_DIR}/artisan sms:update:count"
alias sms-phone="php ${APP_DIR}/artisan sms:update:phone"
alias sms-network="php ${APP_DIR}/artisan sms:update:network"

# ---------------------------------------------------------------------------
# Credits  (cr-*)
# ---------------------------------------------------------------------------
alias cr-reconcile="php ${APP_DIR}/artisan credit:reconcile"
alias cr-install="php ${APP_DIR}/artisan credit:reconcile --action=install -n"
alias cr-import="php ${APP_DIR}/artisan credit:import-csv"

# ---------------------------------------------------------------------------
# Reports  (rpt-*)
# ---------------------------------------------------------------------------
alias rpt-monthly="php ${APP_DIR}/artisan report:sms:monthly"
alias rpt-weekly="php ${APP_DIR}/artisan report:sms:weekly"
alias rpt-weekly-prev="php ${APP_DIR}/artisan report:sms:weekly --prev"
alias rpt-custom="php ${APP_DIR}/artisan report:sms:custom"
alias rpt-credit="php ${APP_DIR}/artisan report:credit:summary"

# ---------------------------------------------------------------------------
# Accounts
# ---------------------------------------------------------------------------
alias auto-merge="php ${APP_DIR}/artisan accounts:auto-merge-duplicates"

# ---------------------------------------------------------------------------
# Maintenance
# ---------------------------------------------------------------------------
alias log-api-clear="php ${APP_DIR}/artisan log:clear:api-log"
alias log-email-clear="php ${APP_DIR}/artisan email:clear-logs"
alias inv-sync="php ${APP_DIR}/artisan invoices:sync:status"
alias csv-import="php ${APP_DIR}/artisan import:csv"
alias data-cache="php ${APP_DIR}/artisan data:cache-structures"
alias sanctum-prune="php ${APP_DIR}/artisan sanctum:prune-expired"

# ---------------------------------------------------------------------------
# Zoho  (zoho-*)
# ---------------------------------------------------------------------------
alias zoho-cust="php ${APP_DIR}/artisan zoho:customer"
alias zoho-inv="php ${APP_DIR}/artisan zoho:invoice"
alias zoho-cust-inv="php ${APP_DIR}/artisan zoho:customer:invoice"
alias zoho-pay="php ${APP_DIR}/artisan zoho:payment"

# ---------------------------------------------------------------------------
# Database — triggers  (dbt-*)
# ---------------------------------------------------------------------------
alias dbt-sms="php ${APP_DIR}/artisan db:trigger:sms"
alias dbt-pay="php ${APP_DIR}/artisan db:trigger:payment"
alias dbt-legacy="php ${APP_DIR}/artisan db:trigger:payment:legacy"

# ---------------------------------------------------------------------------
# Database — views  (dbv-*)
# ---------------------------------------------------------------------------
alias dbv-network="php ${APP_DIR}/artisan db:view:network-report"
alias dbv-sms="php ${APP_DIR}/artisan db:view:sms-report"

# ---------------------------------------------------------------------------
# Database — extensions  (dbx-*)
# ---------------------------------------------------------------------------
alias dbx-install="php ${APP_DIR}/artisan db:extensions install"
alias dbx-remove="php ${APP_DIR}/artisan db:extensions remove"

# ---------------------------------------------------------------------------
# Cache / config / optimise
# ---------------------------------------------------------------------------
alias cache-clear="php ${APP_DIR}/artisan cache:clear"
alias cfg-clear="php ${APP_DIR}/artisan config:clear"
alias cfg-cache="php ${APP_DIR}/artisan config:cache"
alias opt="php ${APP_DIR}/artisan optimize"
alias opt-clear="php ${APP_DIR}/artisan optimize:clear"
alias view-clear="php ${APP_DIR}/artisan view:clear"
alias view-cache="php ${APP_DIR}/artisan view:cache"
alias route-clear="php ${APP_DIR}/artisan route:clear"
alias route-cache="php ${APP_DIR}/artisan route:cache"
