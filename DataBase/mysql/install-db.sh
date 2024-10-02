#!/bin/bash

su_user=root
su_pass=a2billing

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
	printf "CREATE DATABASE IF NOT EXISTS %s DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;\n" "$dbname"
	printf "CREATE USER IF NOT EXISTS %s@localhost IDENTIFIED BY '%s';\n" "$username" "$password"
	printf "GRANT ALL PRIVILEGES ON %s.* TO %s@localhost WITH GRANT OPTION;\n" "$dbname" "$username"
	exit 1
fi

if ! "${cmd[@]}" < "./schema.sql"; then
	printf "Error installing DB schema script\n"
	exit 1
fi
if ! "${cmd[@]}" < "./data.sql"; then
	printf "Error installing initial data\n"
	exit 1
fi

printf "Completed database setup, superuser created with username %s and password %s\n", "$su_user", "$su_pass"
printf "\n"
exit 0
