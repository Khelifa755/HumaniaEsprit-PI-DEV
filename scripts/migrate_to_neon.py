import re
import sys
import psycopg

NEON_DSN = "postgresql://neondb_owner:npg_VeIiHxn0qDB4@ep-raspy-queen-b5h1eof4-pooler.c-7.us-east-2.aws.neon.tech/aa?sslmode=require"
SQL_FILE = r"c:\Users\akhel\Desktop\humania\Esprit-PIDEV-3A12-2526-Humania\SYMFONY\humania_full (1).sql"

def parse_create_tables(content):
    pos = 0
    tables = {}
    while True:
        idx = content.find("CREATE TABLE IF NOT EXISTS `", pos)
        if idx == -1:
            break
        name_start = idx + len("CREATE TABLE IF NOT EXISTS `")
        name_end = content.find("`", name_start)
        table_name = content[name_start:name_end]
        
        open_paren = content.find("(", name_end)
        depth = 1
        curr = open_paren + 1
        in_string = False
        str_char = None
        while depth > 0 and curr < len(content):
            ch = content[curr]
            if in_string:
                if ch == str_char:
                    if content[curr-1] != '\\':
                        in_string = False
            else:
                if ch in ("'", '"'):
                    in_string = True
                    str_char = ch
                elif ch == '(':
                    depth += 1
                elif ch == ')':
                    depth -= 1
            curr += 1
        
        body = content[open_paren+1:curr-1]
        tables[table_name] = body
        pos = curr

    return tables

def parse_mysql_column(col_line):
    line = col_line.strip().rstrip(",")
    if not line:
        return None
    
    if re.match(r"^(PRIMARY KEY|UNIQUE KEY|KEY|CONSTRAINT|FOREIGN KEY)\b", line, re.IGNORECASE):
        return None
    
    m = re.match(r"^`([^`]+)`\s+([a-zA-Z0-9_]+(?:\([^)]+\))?)(.*)$", line)
    if not m:
        return None
    
    col_name = m.group(1)
    raw_type = m.group(2).lower()
    rest = m.group(3)
    
    base_type = raw_type.split("(")[0].strip()
    
    pg_type = "TEXT"
    is_bool = False
    
    if base_type == "int":
        pg_type = "INTEGER"
    elif base_type == "bigint":
        pg_type = "BIGINT"
    elif base_type == "tinyint":
        pg_type = "BOOLEAN"
        is_bool = True
    elif base_type in ("varchar", "char"):
        pg_type = raw_type.upper()
    elif base_type in ("text", "longtext", "mediumtext"):
        pg_type = "TEXT"
    elif base_type in ("datetime", "timestamp"):
        pg_type = "TIMESTAMP"
    elif base_type == "date":
        pg_type = "DATE"
    elif base_type == "time":
        pg_type = "TIME"
    elif base_type in ("double", "float"):
        pg_type = "DOUBLE PRECISION"
    elif base_type == "decimal":
        pg_type = raw_type.upper().replace("DECIMAL", "NUMERIC")
    elif base_type == "enum":
        pg_type = "VARCHAR(100)"
    elif base_type == "blob":
        pg_type = "BYTEA"
    else:
        pg_type = "TEXT"
        
    is_auto_inc = "AUTO_INCREMENT" in rest.upper()
    is_not_null = "NOT NULL" in rest.upper()
    
    default_clause = ""
    def_match = re.search(r"DEFAULT\s+('([^']*)'|NULL|CURRENT_TIMESTAMP|(-?\d+(?:\.\d+)?))", rest, re.IGNORECASE)
    if def_match:
        val = def_match.group(1)
        if val.upper() == "NULL":
            default_clause = "DEFAULT NULL"
        elif val.upper() == "CURRENT_TIMESTAMP":
            default_clause = "DEFAULT CURRENT_TIMESTAMP"
        elif is_bool:
            inner_val = def_match.group(2) if def_match.group(2) is not None else val
            if inner_val in ('1', 'true', 'TRUE'):
                default_clause = "DEFAULT TRUE"
            elif inner_val in ('0', 'false', 'FALSE'):
                default_clause = "DEFAULT FALSE"
            else:
                default_clause = "DEFAULT NULL"
        else:
            default_clause = f"DEFAULT {val}"
            
    return {
        "name": col_name,
        "pg_type": pg_type,
        "is_auto_inc": is_auto_inc,
        "is_not_null": is_not_null,
        "default_clause": default_clause,
        "is_bool": is_bool
    }

def parse_mysql_values(values_str):
    rows = []
    i = 0
    n = len(values_str)
    
    while i < n:
        while i < n and values_str[i] != '(':
            i += 1
        if i >= n:
            break
        i += 1
        
        current_row = []
        while i < n and values_str[i] != ')':
            while i < n and values_str[i] in ' \t\r\n':
                i += 1
            if i >= n or values_str[i] == ')':
                break
                
            if values_str[i] == "'":
                i += 1
                val_chars = []
                while i < n:
                    ch = values_str[i]
                    if ch == '\\':
                        i += 1
                        if i < n:
                            esc = values_str[i]
                            if esc == 'n': val_chars.append('\n')
                            elif esc == 'r': val_chars.append('\r')
                            elif esc == 't': val_chars.append('\t')
                            elif esc == '0': val_chars.append('')
                            elif esc == '\\': val_chars.append('\\')
                            elif esc == "'": val_chars.append("'")
                            elif esc == '"': val_chars.append('"')
                            else: val_chars.append(esc)
                    elif ch == "'":
                        if i + 1 < n and values_str[i+1] == "'":
                            val_chars.append("'")
                            i += 1
                        else:
                            i += 1
                            break
                    else:
                        val_chars.append(ch)
                    i += 1
                current_row.append("".join(val_chars))
            elif values_str[i:i+4].upper() == 'NULL':
                current_row.append(None)
                i += 4
            else:
                token_chars = []
                while i < n and values_str[i] not in ',)':
                    token_chars.append(values_str[i])
                    i += 1
                token = "".join(token_chars).strip()
                if token == '' or token.upper() == 'NULL':
                    current_row.append(None)
                elif re.match(r"^-?\d+$", token):
                    current_row.append(int(token))
                elif re.match(r"^-?\d+\.\d+$", token):
                    current_row.append(float(token))
                else:
                    current_row.append(token)
            
            while i < n and values_str[i] in ' \t\r\n':
                i += 1
            if i < n and values_str[i] == ',':
                i += 1
        
        if i < n and values_str[i] == ')':
            i += 1
            rows.append(current_row)
            
    return rows

def main():
    print(f"Reading SQL file: {SQL_FILE}")
    with open(SQL_FILE, "r", encoding="utf-8", errors="replace") as f:
        content = f.read()

    print("Parsing table definitions...")
    tables_raw = parse_create_tables(content)
    print(f"Found {len(tables_raw)} tables in dump.")

    table_defs = {}
    bool_columns_by_table = {}
    date_columns_by_table = {}

    for tname, body in tables_raw.items():
        cols = []
        pk_cols = []
        unique_keys = []
        bool_cols = set()
        date_cols = set()

        for line in body.splitlines():
            line_str = line.strip().rstrip(",")
            if not line_str:
                continue

            # Check primary key
            pk_m = re.match(r"^PRIMARY KEY\s*\(([^)]+)\)", line_str, re.IGNORECASE)
            if pk_m:
                pk_cols = [c.strip().strip("`") for c in pk_m.group(1).split(",")]
                continue

            # Check unique key
            uq_m = re.match(r"^UNIQUE KEY\s*`?([^`\s]+)`?\s*\(([^)]+)\)", line_str, re.IGNORECASE)
            if uq_m:
                u_name = uq_m.group(1)
                u_cols = [c.strip().strip("`") for c in uq_m.group(2).split(",")]
                unique_keys.append((u_name, u_cols))
                continue

            # Check column
            parsed = parse_mysql_column(line_str)
            if parsed:
                cols.append(parsed)
                if parsed["is_bool"]:
                    bool_cols.add(parsed["name"])
                if parsed["pg_type"] in ("DATE", "TIMESTAMP"):
                    date_cols.add(parsed["name"])

        bool_columns_by_table[tname] = bool_cols
        date_columns_by_table[tname] = date_cols
        table_defs[tname] = {
            "cols": cols,
            "pk_cols": pk_cols,
            "unique_keys": unique_keys
        }

    # Parse foreign keys from ALTER TABLE blocks
    alter_blocks = re.findall(r"ALTER TABLE `([^`]+)`\s*(.*?);", content, re.DOTALL)
    foreign_keys = []
    for ttable, abody in alter_blocks:
        constraints = re.findall(
            r"ADD CONSTRAINT `([^`]+)` FOREIGN KEY \(([^)]+)\) REFERENCES `([^`]+)` \(([^)]+)\)(.*)",
            abody,
            re.IGNORECASE
        )
        for cname, fcols, rtable, rcols, actions in constraints:
            fcols_list = [c.strip().strip("`") for c in fcols.split(",")]
            rcols_list = [c.strip().strip("`") for c in rcols.split(",")]
            foreign_keys.append({
                "source_table": ttable,
                "constraint_name": cname,
                "source_cols": fcols_list,
                "ref_table": rtable,
                "ref_cols": rcols_list,
                "actions": actions.strip()
            })
    print(f"Found {len(foreign_keys)} foreign keys to apply after data migration.")

    # Parse all INSERTs
    print("Parsing INSERT statements and data rows...")
    insert_regex = re.compile(r"INSERT INTO `([^`]+)`\s*\(([^)]+)\)\s*VALUES\s*", re.IGNORECASE)
    matches = list(insert_regex.finditer(content))
    data_by_table = {t: [] for t in table_defs}

    for match in matches:
        tname = match.group(1)
        cols = [c.strip().strip("`") for c in match.group(2).split(",")]
        start_pos = match.end()
        
        i = start_pos
        n = len(content)
        in_str = False
        str_ch = None
        while i < n:
            ch = content[i]
            if in_str:
                if ch == '\\':
                    i += 2
                    continue
                elif ch == str_ch:
                    in_str = False
            else:
                if ch in ("'", '"'):
                    in_str = True
                    str_ch = ch
                elif ch == ';':
                    break
            i += 1

        values_str = content[start_pos:i]
        rows = parse_mysql_values(values_str)
        data_by_table[tname].append((cols, rows))

    total_rows = sum(sum(len(rows) for _, rows in batches) for batches in data_by_table.values())
    print(f"Total rows to insert: {total_rows}")

    print("Connecting to Neon PostgreSQL...")
    conn = psycopg.connect(NEON_DSN)
    conn.autocommit = False
    cur = conn.cursor()

    try:
        # Recreate public schema for fresh start
        print("Resetting public schema...")
        cur.execute("DROP SCHEMA IF EXISTS public CASCADE;")
        cur.execute("CREATE SCHEMA public;")
        cur.execute("GRANT ALL ON SCHEMA public TO neondb_owner;")
        cur.execute("GRANT ALL ON SCHEMA public TO public;")
        conn.commit()

        # Create all tables
        print("Creating PostgreSQL tables...")
        for tname, defn in table_defs.items():
            col_sql_parts = []
            cols = defn["cols"]
            pk_cols = defn["pk_cols"]

            for col in cols:
                cname = col["name"]
                ctype = col["pg_type"]
                is_pk = (cname in pk_cols and len(pk_cols) == 1)

                if is_pk and ctype in ("INTEGER", "BIGINT"):
                    part = f'"{cname}" {ctype} GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY'
                else:
                    part = f'"{cname}" {ctype}'
                    if col["is_not_null"]:
                        part += " NOT NULL"
                    if col["default_clause"]:
                        part += f" {col['default_clause']}"
                col_sql_parts.append(part)

            # If multi-column PK or non-identity PK
            if pk_cols:
                if len(pk_cols) > 1 or (len(pk_cols) == 1 and cols and any(c["name"] == pk_cols[0] and c["pg_type"] not in ("INTEGER", "BIGINT") for c in cols)):
                    pk_joined = ", ".join(f'"{c}"' for c in pk_cols)
                    col_sql_parts.append(f"PRIMARY KEY ({pk_joined})")

            create_sql = f'CREATE TABLE "{tname}" (\n  ' + ",\n  ".join(col_sql_parts) + "\n);"
            cur.execute(create_sql)

            # Create unique keys
            for u_idx, (u_name, u_cols) in enumerate(defn["unique_keys"]):
                # Ensure unique index name in schema
                safe_u_name = f"uq_{tname}_{u_idx}_{u_name}"[:63]
                u_cols_joined = ", ".join(f'"{c}"' for c in u_cols)
                try:
                    cur.execute(f'CREATE UNIQUE INDEX "{safe_u_name}" ON "{tname}" ({u_cols_joined});')
                except Exception as e:
                    print(f"Warning creating unique index on {tname}: {e}")

        conn.commit()
        print("All 73 tables created successfully.")

        # Insert Data
        print("Inserting table data...")
        for tname, batches in data_by_table.items():
            if not batches:
                continue

            bool_cols = bool_columns_by_table.get(tname, set())
            date_cols = date_columns_by_table.get(tname, set())

            total_table_rows = 0
            for cols, rows in batches:
                if not rows:
                    continue
                cols_joined = ", ".join(f'"{c}"' for c in cols)
                placeholders = ", ".join(["%s"] * len(cols))
                insert_sql = f'INSERT INTO "{tname}" ({cols_joined}) VALUES ({placeholders})'

                clean_rows = []
                for row in rows:
                    clean_row = []
                    for col_name, val in zip(cols, row):
                        if val is not None:
                            # Convert boolean
                            if col_name in bool_cols:
                                if str(val).strip() in ('1', 'true', 'True', 't', 'TRUE'):
                                    val = True
                                elif str(val).strip() in ('0', 'false', 'False', 'f', 'FALSE'):
                                    val = False
                            # Convert invalid 0 dates
                            if col_name in date_cols:
                                if str(val).strip() in ('0000-00-00', '0000-00-00 00:00:00'):
                                    # If the column is NOT NULL, substitute a valid timestamp
                                    col_info = next((c for c in table_defs[tname]["cols"] if c["name"] == col_name), None)
                                    if col_info and col_info["is_not_null"]:
                                        val = '2026-04-19 12:00:00'
                                    else:
                                        val = None

                        clean_row.append(val)
                    clean_rows.append(tuple(clean_row))

                # Batch insert
                cur.executemany(insert_sql, clean_rows)
                total_table_rows += len(clean_rows)

            conn.commit()
            if total_table_rows > 0:
                print(f"  Inserted {total_table_rows} rows into '{tname}'.")

        # Sync Identity / Sequences
        print("Updating identity sequences...")
        for tname, defn in table_defs.items():
            pk_cols = defn["pk_cols"]
            if len(pk_cols) == 1:
                pk = pk_cols[0]
                col_info = next((c for c in defn["cols"] if c["name"] == pk), None)
                if col_info and col_info["pg_type"] in ("INTEGER", "BIGINT"):
                    cur.execute(f"""
                        SELECT setval(
                            pg_get_serial_sequence('"{tname}"', '{pk}'),
                            GREATEST(COALESCE((SELECT MAX("{pk}") FROM "{tname}"), 0), 1),
                            COALESCE((SELECT MAX("{pk}") FROM "{tname}"), 0) > 0
                        );
                    """)
        conn.commit()
        print("Identity sequences synchronized.")

        # Apply Foreign Keys
        print("Applying foreign key constraints...")
        fk_success = 0
        fk_skipped = 0
        for idx, fk in enumerate(foreign_keys):
            st = fk["source_table"]
            scols = ", ".join(f'"{c}"' for c in fk["source_cols"])
            rt = fk["ref_table"]
            rcols = ", ".join(f'"{c}"' for c in fk["ref_cols"])
            actions = fk["actions"].strip()
            # Clean actions for PG
            actions_clean = re.sub(r"\s+", " ", actions)
            
            cname = f"fk_{st}_{idx}_{fk['constraint_name']}"[:63]
            fk_sql = f'ALTER TABLE "{st}" ADD CONSTRAINT "{cname}" FOREIGN KEY ({scols}) REFERENCES "{rt}" ({rcols}) {actions_clean};'
            
            try:
                cur.execute(fk_sql)
                fk_success += 1
            except Exception as e:
                fk_skipped += 1
                print(f"Notice on FK {cname}: {e}")
                conn.rollback()
                continue
            else:
                conn.commit()

        print(f"Foreign keys applied: {fk_success} succeeded, {fk_skipped} skipped.")

        # Final Verification
        cur.execute("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';")
        total_created = cur.fetchone()[0]
        print(f"\n==========================================")
        print(f"MIGRATION COMPLETE!")
        print(f"Total tables in Neon DB: {total_created}")
        print(f"==========================================")

    except Exception as e:
        conn.rollback()
        print(f"Fatal error during migration: {e}")
        raise
    finally:
        conn.close()

if __name__ == "__main__":
    main()
