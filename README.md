# Github Gists Demo

A demo script to query a user's publicly available gists from Github's API. 

On the initial run the script will cache the datetime the script was run at. On subsequent runs this 
datetime will be used to show only the gists created after the last run. If you want to reset this cached value 
then pass the `--reset` flag to the script.

Depending on how you want to consume the result, you can choose to set the output format by passing the `--format` flag 
with either `json` or `tabular`, with the default being json. 

# Build the script

Dependencies: Docker

From the directory containing the code:

`docker build . -t gist-demo`

# Running the script

`docker run -it --rm -v $(pwd)/data:/app/data gist-demo bin/console get-user-gists <username>`

There are several flags you can pass to the script to influence pagination, and the cached since value. 

To list them use:

`docker run -it --rm -v $(pwd)/data:/app/data gist-demo bin/console get-user-gists --help`

# Examples:

Queries the Github API one Gist at a time, collating the results.

`docker run -it --rm -v $(pwd)/data:/app/data gist-demo bin/console get-user-gists markbtaylor --page=1 --perPage=1`

Resets the previous cached since time.

`docker run -it --rm -v $(pwd)/data:/app/data gist-demo bin/console get-user-gists markbtaylor --reset`

Output as a table to the console, as opposed to JSON.

`docker run -it --rm -v $(pwd)/data:/app/data gist-demo bin/console get-user-gists markbtaylor --format=tabular`
