## Travel API – The perfect solution for managing trips and reviews
### Travel api about the project
#### Our service provides a convenient tool for administrators and users:
- ✅ Flexible tour management – ​​the administrator can add trips, link tours to them and upload images.
- ✅ Interactive reviews – users can leave comments with photos, making the experience more lively and authentic.
- ✅ Content moderation – the administrator controls and publishes comments, ensuring quality content.
- ✅ Automatic notifications – the author of the comment receives an email when his review is published.

#### Project functionality 🚀
1. `The project is implemented using the REST API concept.`
2. `Uploading, editing, and publishing of images is supported.`
3. `Automatic image compression without loss of quality during upload is implemented.`
4. `When uploading an image for comments, a queue is used to quickly send the result.`
5. `After publishing a comment, the author will receive an email notification that it was successfully published.`
6. `62 functional tests written`
7. `Swagger  Documentation`
8. `Creating an admin via console command`
9. `implemented queues`
10. `Docker is used and makefile`
11. `using ESC`


### Launch of the project 🔌
```
git clone git@github.com:ArtemDidyk-Dev/travel-api.git
```
```
copy .env.example > .env
```
```
make build
```
#### Adding a user (administrator) 🛡️
```
make create-user
```
#### Start queue 🚶‍♂️🚶‍♂️🚶‍♂️
```
make start-queue
```

## Link project 🏁
```
http://travel-api.localhost/api/v1/travels/
```

## Link Documentation 📁
```
http://travel-api.localhost/api/documentation
```

# Additional information 🧐

#### Adding fixtures for a test 🧪
```
make create-test-db
make migrate-test-db
```
#### Start test  🛠️ 
```
make test
make test-filter
```
#### Generate Documentation 📗
```
make generate-doc
```
#### Start esc ✏️
```
make ecs
```
#### Link to email services ✉️
```
http://travel-api.localhost:8025/
```

#### Link to phpMyAdmin 📝
```
http://localhost:8090/
```

#### Start project 🟢 
```
make run
```
#### Stop project 🔴
```
make stop
```
