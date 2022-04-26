# Commands

Get all Version control directories that are registered in phpstorm and add them as a scope. And give them a scope color.
Respects existing scopes/colors. Will not act on the root directory.

    marowak phpstorm:scopes:sync ~/localwp/


# Todo

[] create `wp-cli.yml` in project root.
[] Set xdebug settings.
[] double scan for `localhost` dbhost. I need to point to the socket.
[] Scan for git(and svn?) repos and add.
[] Remove moved/deleted git repos.
[] mark as WP project and set correct WP root.
[] create .desktop launcher file.
[] Set SFTP deployment based on `wp-cli.yml` file.
