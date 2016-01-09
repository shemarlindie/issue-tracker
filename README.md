# V75 Issue Tracker #
### Project management and issue tracking. ###
#### by V75 Apps ####

http://dev.version75.com/issue-tracker/#/

#### RECOMMENDATIONS: ####
* Install Node
* Install bower globally: npm install -g bower
* Install gulp globally: npm install -g gulp

## Project Setup ##
----

To install dev dependencies: 
> npm install

To install app dependencies **(run in the "src/" folder)**:
> bower install

#### BUILD PROCESS ####
To build app resources during development (less, etc..)
> gulp build

To build and watch for changes:
> gulp

To build for deployment:
> gulp dist

This will create the "dist/" folder with all essential app files to be uploaded to a server. (with minified JS)


#### NOTE ####
Unless stated otherwise, all commands must be run in the project's root folder.