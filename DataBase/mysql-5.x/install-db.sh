#!/bin/bash

script_dir=$(dirname "$0")
sqllist=(
    a2billing-schema-v1.4.0.sql UPDATE-a2billing-v1.4.0-to-v1.4.1.sql
    UPDATE-a2billing-v1.4.1-to-v1.4.2.sql UPDATE-a2billing-v1.4.2-to-v1.4.3.sql
    UPDATE-a2billing-v1.4.3-to-v1.4.4.sql UPDATE-a2billing-v1.4.4-to-v1.4.4.1.sql
    UPDATE-a2billing-v1.4.4.1-to-v1.4.5.sql UPDATE-a2billing-v1.4.5-to-v1.5.0.sql
    UPDATE-a2billing-v1.5.0-to-v1.5.1.sql UPDATE-a2billing-v1.5.1-to-v1.6.0.sql
    UPDATE-a2billing-v1.6.0-to-v1.6.1.sql UPDATE-a2billing-v1.6.1-to-v1.6.2.sql
    UPDATE-a2billing-v1.6.2-to-v1.7.0.sql UPDATE-a2billing-v1.7.0-to-v1.7.1.sql
    UPDATE-a2billing-v1.7.1-to-v1.7.2.sql UPDATE-a2billing-v1.7.2-to-v1.8.0.sql
    UPDATE-a2billing-v1.8.0-to-v1.8.1.sql UPDATE-a2billing-v1.8.1-to-v1.8.2.sql
    UPDATE-a2billing-v1.8.2-to-v1.8.3.sql UPDATE-a2billing-v1.8.3-to-v1.8.4.sql
    UPDATE-a2billing-v1.8.4-to-v1.8.5.sql UPDATE-a2billing-v1.8.5-to-v1.8.6.sql
    UPDATE-a2billing-v1.8.6-to-v1.9.0.sql UPDATE-a2billing-v1.9.0-to-v1.9.1.sql
    UPDATE-a2billing-v1.9.1-to-v1.9.2.sql UPDATE-a2billing-v1.9.2-to-v1.9.3.sql
    UPDATE-a2billing-v1.9.3-to-v1.9.4.sql UPDATE-a2billing-v1.9.4-to-v1.9.5.sql
    UPDATE-a2billing-v1.9.5-to-v2.0.sql UPDATE-a2billing-v2.0-to-v2.0.3.sql
    UPDATE-a2billing-v2.0.3-to-v2.0.4.sql UPDATE-a2billing-v2.0.4-to-v2.0.5.sql
    UPDATE-a2billing-v2.0.5-to-v2.0.6.sql UPDATE-a2billing-v2.0.6-to-v2.0.7.sql
    UPDATE-a2billing-v2.0.7-to-v2.0.8.sql UPDATE-a2billing-v2.0.9-to-v2.0.10.sql
    UPDATE-a2billing-v2.0.10-to-v2.0.11.sql UPDATE-a2billing-v2.0.11-to-v2.0.12.sql
    UPDATE-a2billing-v2.0.12-to-v2.0.13.sql UPDATE-a2billing-v2.0.13-to-v2.0.14.sql
    UPDATE-a2billing-v2.0.14-to-v2.0.15.sql UPDATE-a2billing-v2.0.15-to-v2.0.16.sql
    UPDATE-a2billing-v2.0.16-to-v2.0.17.sql UPDATE-a2billing-v2.0.17-to-v2.1.0.sql
    UPDATE-a2billing-v2.1.0-to-v2.1.1.sql UPDATE-a2billing-v2.1.1-to-v2.1.2.sql
    UPDATE-a2billing-v2.1.2-to-v2.1.3.sql UPDATE-a2billing-v2.1.3-to-v2.1.4.sql
    UPDATE-a2billing-v2.1.4-to-v2.2.0.sql UPDATE-a2billing-v2.2.0-to-v2.3.0.sql
    UPDATE-a2billing-v2.3.0-to-v3.0.0.sql
)

printf "\n"
printf "Install A2Billing Database\n"
printf -- "--------------------------\n"
printf "\n"

printf "Enter Database Name: "
read -r dbname

printf "Enter Hostname: "
read -r hostname

printf "Enter Username: "
read -r username

printf "Enter Password: "
read -r password

cmd=(mysql)
if [[ -n $username ]]; then
	cmd+=("--user=$username")
fi
if [[ -n $password ]]; then
	cmd+=("--password=$password")
fi
if [[ -n $hostname ]]; then
	cmd+=("--host=$hostname")
fi
if [[ -n $dbname ]]; then
	cmd+=("--database=$dbname")
fi

printf "\nUsing command: %s\n\n" "${cmd[*]}"

if ! "${cmd[@]}" --execute='SELECT 1' &> /dev/null; then
	printf "Check database %s and user %s exist before running script:\n" "$dbname" "$username"
	printf "CREATE DATABASE IF NOT EXISTS %s;\n" "$dbname"
	printf "CREATE USER IF NOT EXISTS %s@localhost IDENTIFIED BY '%s';\n" "$username" "$password"
	printf "GRANT ALL PRIVILEGES ON %s.* TO %s@localhost WITH GRANT OPTION;\n" "$dbname" "$username"
	exit 1
fi

for script in "${sqllist[@]}"; do
	printf .
	if ! "${cmd[@]}" < "$script_dir/$script"; then
		printf "Error running script %s\n" "$script"
		exit 1
	fi
done
printf "\n"

exit 0
