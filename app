#!/bin/bash

# Function to change ownership of files and directories
change_ownership() {
    local target_user="$1"
    chown -R "$target_user:$target_user" ./
    echo "Ownership changed to $target_user for all files and directories in the current directory."
}

xdebug_off() {
  docker compose down
  # Assign the filename
  filename="docker/php/php/php.ini"
  sed -i "s/xdebug.start_with_request = yes/xdebug.start_with_request = no/" $filename
  docker compose up -d
}

xdebug_on() {
  docker compose down
  # Assign the filename
  filename="docker/php/php/php.ini"
  sed -i "s/xdebug.start_with_request = no/xdebug.start_with_request = yes/" $filename
  docker compose up -d
}

if [ "$1" = "cs" ]; then
    # Execute composer run cs inside the container named 'php'
    docker exec -it php composer run cs
elif [ "$1" = "stan" ]; then
    # Execute composer run stan inside the container named 'php'
    docker exec -it php composer run stan
elif [ "$1" = "ownership" ]; then
    # Get the target username (use the user who executed the script if no second argument)
    target_user=${2:-$(whoami)}

    # Call the function to change ownership
    change_ownership "$target_user"
elif [ "$1" = "xdebug-off" ]; then
    xdebug_off
elif [ "$1" = "xdebug-on" ]; then
    xdebug_on
else
    command="$1"

    # Shift to remove the first argument, leaving only the command and its arguments
    shift

    # Execute bin/console with the given command and its arguments
    docker exec -it php "$command" "$@"
fi
