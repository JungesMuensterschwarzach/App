> [!NOTE]  
> This project is deprecated and not in active use anymore.

<p align="center">
  <a href="https://github.com/JungesMuensterschwarzach/App">
    <img src="./app/public/icons/512x512.png" alt="Logo" width="200" height="200">
  </a>

  <h3 align="center">Junges Münsterschwarzach App</h3>

  <p align="center">
    This repository contains the source code of the components related to the community app of Junges Münsterschwarzach.
  </p>
</p>



<!-- ABOUT THE PROJECT -->
## About The Project

This respository contains the components related to the community app of Junges Münsterschwarzach:
* [API](./api): new app backend (WIP)
* [APP](./app): app frontend (main component)
* [COMMONS](./commons): contains shared ("common") datastructures and methods for both front- and backend
* [DOCUMENTATION](./documentation): documents, presentations and mock-ups about the app
* [POSTMAN](./postman): automated tests for the API (WIP)
* [PROXY](./proxy): a simple nginx proxy for the whole project
* [TEMPLATES](./templates): contains blueprints for setting up your environment
* [TOOLS](./tools): utility tools for automated database updates as well as dictionary generation
* [WEBSERVICE](./webservice): the current backend and administration area (going to be replaced by the API (backend) and a new app update (introducing the administration area inside the app instead of a standalone website)



<!-- GETTING STARTED -->
## Getting Started

To get a local copy up and running follow these simple steps.

### Prerequisites

["All you need is LOVE!"](https://youtu.be/_7xMfIp-irg) - and [Docker](https://www.docker.com/) as well as [Git](https://git-scm.com/)!

### Installation

Once docker is installed, you must follow these steps:

1. Clone the repo
   ```sh
   git clone https://github.com/JungesMuensterschwarzach/App.git
   ```
2. Set up your environment (variables):
    * <strong>JMA_BUILD_TYPE</strong>: "local"
    * <strong>JMA_DATABASE</strong>: a directory where you want to persist the database content
    * <strong>JMA_IMAGES</strong>: a directory where you want to persist the uploaded images
    * <strong>JMA_SECRETS</strong>: a directory that contains a directory "local" with secrets for both the front- and backend (see [templates](./templates/jma_secrets/local))
        * <i>[app.json](./templates/jma_secrets/local/app.json)</i>: contains link settings (that should be fine for local development) and the public push notification key (can be generated with [WebPush](https://github.com/web-push-libs/web-push))
        * <i>[database.json](./templates/jma_secrets/local/database.json)</i>: adjust to your local database settings
        * <i>[mail.json](./templates/jma_secrets/local/mail.json)</i>: enter the connection credentials for a mail account (you don't need this, if you don't want to send mails)
        * <i>[mapbox.json](./templates/jma_secrets/local/mapbox.json)</i>: enter your [mapbox](https://www.mapbox.com) api key here
        * <i>[push_notification.json](./templates/jma_secrets/local/push_notification.json)</i>: enter your webpush private and public key here (same as in app.json)
        * <i>[server.json](./templates/jma_secrets/local/server.json)</i>: general server settings (you don't need to change anything)
    * <strong>MYSQL_SECRETS</strong>: point to a directory with a file named "[root_pass](./templates/mysql_secrets/root_pass)" that contains your chosen MYSQL root password

3. Copy the app.json and mapbox.json from your "<strong>JMA_SECRETS</strong>/local" directory to the "./app/public" directory.

4. Copy the "package.json" from the base directory to the "./app" directory.

5. Build or pull the component images
    ```sh
    docker-compose -f docker-compose.yml -f docker-compose-local.yml build
    ```
6. Start the docker run
    ```sh
    docker-compose -f docker-compose.yml -f docker-compose-local.yml up -d
    ```
7. Login to phpmyadmin, create database `junges_muensterschwarzach_app` and import sql file `./tools/database_update_scripts/0.sql`.


<!-- USAGE EXAMPLES -->
## Usage

Once docker is running, you can access:
* the app via http://localhost/app
* the app backend via http://localhost/app-backend
* phpmyadmin via http://localhost/phpmyadmin



<!-- ROADMAP -->
## Roadmap

* backend rework
    * replacing the current PHP backend and standalone administration site by new API and frontend integration of the administration features
* community interactivity
    * comments (for events, news etc.)
    * profiles
    * messenger
    * ride sharing from/to events
    * file sharing for event related material
    * survey/feedback system
* news
    * separate news channel subscribing



<!-- LICENSE -->
## License

Distributed under the GNU Affero General Public License. See [LICENSE](./LICENSE) for more information.



<!-- CONTACT -->
## Contact

Lucas Roth - lucas@luckev.info

Project Link: [https://github.com/JungesMuensterschwarzach/App](https://github.com/JungesMuensterschwarzach/App)



<!-- ACKNOWLEDGEMENTS -->
## Acknowledgements

* frontend based on the well-known [create-react-app](https://github.com/facebook/create-react-app) - written in [TypeScript](https://github.com/microsoft/TypeScript) while using [react](https://github.com/facebook/react) and [material-ui](https://github.com/mui-org/material-ui) as the main frontend libraries
* backend written in pure [PHP](https://www.php.net/) (going to be replaced by [TypeScript](https://github.com/microsoft/TypeScript) soon)