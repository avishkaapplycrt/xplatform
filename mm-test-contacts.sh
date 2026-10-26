#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Mock Master test contacts
#
# Sets ONE email / phone / country code on every Mock Master student, so test
# emails, SMS and WhatsApp messages from XPlatform go to you instead of real
# students. Run it AFTER "Sync Data" (a sync brings back the live details).
#
# Usage (Git Bash, from anywhere):
#   ./mm-test-contacts.sh                      # uses the values below
#   ./mm-test-contacts.sh -e me@mail.com -p 0412345678 -c +61
#   ./mm-test-contacts.sh -y                   # skip the "are you sure" question
#
# Tables updated: mm_studentuser, mm_deleted_students (email, phone, country_code).
# Not touched: normalized_email (must be unique per student) and login history.
# Note: phone is stored as a number, so a leading 0 is dropped (0406870923 -> 406870923).
# ---------------------------------------------------------------------------

# ===== Change these to your test details =====
EMAIL="xplatformintern@gmail.com"
PHONE="0406870923"
COUNTRY_CODE="+61"
# =============================================

YES=0
while getopts "e:p:c:yh" opt; do
  case "$opt" in
    e) EMAIL="$OPTARG" ;;
    p) PHONE="$OPTARG" ;;
    c) COUNTRY_CODE="$OPTARG" ;;
    y) YES=1 ;;
    h) sed -n '2,20p' "$0"; exit 0 ;;
    *) echo "Unknown option. Use -h for help."; exit 1 ;;
  esac
done

# --- clean and check the values (also keeps the SQL safe) ---
EMAIL="$(echo "$EMAIL" | tr -d '[:space:]')"
PHONE="$(echo "$PHONE" | tr -d ' -')"
COUNTRY_CODE="$(echo "$COUNTRY_CODE" | tr -d '[:space:]')"
[[ "$COUNTRY_CODE" =~ ^[0-9]+$ ]] && COUNTRY_CODE="+$COUNTRY_CODE"

if ! [[ "$EMAIL" =~ ^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$ ]]; then
  echo "Error: '$EMAIL' is not a valid email address."; exit 1
fi
if ! [[ "$PHONE" =~ ^[0-9]{6,15}$ ]]; then
  echo "Error: phone must be 6-15 digits (got '$PHONE')."; exit 1
fi
if ! [[ "$COUNTRY_CODE" =~ ^\+[0-9]{1,4}$ ]]; then
  echo "Error: country code must look like +61 (got '$COUNTRY_CODE')."; exit 1
fi

# --- database settings from the project's .env ---
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
ENV_FILE="$SCRIPT_DIR/.env"
[ -f "$ENV_FILE" ] || { echo "Error: .env not found next to this script ($ENV_FILE)."; exit 1; }
env_value() { grep -E "^$1=" "$ENV_FILE" | head -1 | cut -d= -f2- | tr -d '\r' | sed -e 's/^"//' -e 's/"$//'; }

DB_HOST="$(env_value DB_HOST)"; DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="$(env_value DB_PORT)"; DB_PORT="${DB_PORT:-3306}"
DB_NAME="$(env_value DB_DATABASE)"
DB_USER="$(env_value DB_USERNAME)"; DB_USER="${DB_USER:-root}"
DB_PASS="$(env_value DB_PASSWORD)"

MYSQL="mysql"
command -v mysql >/dev/null 2>&1 || MYSQL="/c/xampp/mysql/bin/mysql.exe"
[ -x "$MYSQL" ] || command -v "$MYSQL" >/dev/null 2>&1 || { echo "Error: mysql client not found (is XAMPP installed at C:\\xampp?)."; exit 1; }

run_sql() {
  MYSQL_PWD="$DB_PASS" "$MYSQL" -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -N -B "$DB_NAME" -e "$1"
}

# Only touch tables that exist in this database.
has_table() { [ "$(run_sql "SHOW TABLES LIKE '$1';")" = "$1" ]; }

echo "Database : $DB_NAME on $DB_HOST:$DB_PORT"
echo "New email: $EMAIL"
echo "New phone: $COUNTRY_CODE $PHONE"
echo
STUDENTS="$(run_sql "SELECT COUNT(*) FROM mm_studentuser;")" || { echo "Error: could not read mm_studentuser. Is MySQL running in XAMPP?"; exit 1; }
echo "Students that will be updated: $STUDENTS"

if [ "$YES" -ne 1 ]; then
  read -r -p "Update every Mock Master student with these details? [y/N] " answer
  [[ "$answer" =~ ^[Yy]$ ]] || { echo "Cancelled. Nothing changed."; exit 0; }
fi

for table in mm_studentuser mm_deleted_students; do
  if has_table "$table"; then
    changed="$(run_sql "UPDATE \`$table\` SET email = '$EMAIL', phone = $PHONE, country_code = '$COUNTRY_CODE'; SELECT ROW_COUNT();")"
    echo "  $table: $changed row(s) changed"
  else
    echo "  $table: not in this database, skipped"
  fi
done

echo
echo "Check:"
run_sql "SELECT CONCAT('  ', COUNT(*), ' students now use ', email, ' / ', country_code, ' ', phone) FROM mm_studentuser GROUP BY email, country_code, phone ORDER BY COUNT(*) DESC LIMIT 3;"
echo "Done. Run this again after every 'Sync Data', because a sync restores the live details."
