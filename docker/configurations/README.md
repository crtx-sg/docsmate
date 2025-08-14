# Docsgo

### Installation
Docsgo is very easy to install and deploy in a Docker container.

By default, the Docker will expose port 80, so change this within the Dockerfile if necessary. 

```sh
$ docker load < docsgo.tar.gz
$ docker load < docsgo_db.tar.gz
$ docker stack deploy -c ./docker-compose.yml docsgo
```

Verify the deployment by navigating to your server address in your preferred browser.
[http://localhost/](http://localhost)

Default username: user@docsgo.com and password docsgo@123

Default password for new users can be changed in .env file PASS_CODE