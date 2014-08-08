alfred-devdocs
==============

Alfred workflow for devdocs.io documentations. 

# How to
## Find in a specific documentation
Keywords exist for each documentation supported by DevDocs.

![AngularJS documentation](http://content.screencast.com/users/yannickglt/folders/Snagit/media/7492bbba-99b7-4a75-9b97-dfba08437d24/2014-08-04_14-40-20.png)

## Completion

![Filter on functions](http://content.screencast.com/users/yannickglt/folders/Snagit/media/eb943219-5275-4cf8-a915-a97ea1772fa0/2014-08-04_14-43-13.png)

## Global search
Global search may be slow the first time it is called because it will download all the documentations at once.

![Global search](http://content.screencast.com/users/yannickglt/folders/Snagit/media/c6d429e4-2499-4764-91e5-06dba18ff392/2014-08-04_14-46-31.png)

## Alfred preview
Using the "shift" key on a function will display a preview (using quicklook) of the doc.
![Preview](http://content.screencast.com/users/yannickglt/folders/Snagit/media/a339c2aa-a75d-4316-a8fb-d0d75e932912/2014-08-04_14-50-44.png)

# Install
```sh
$ curl -L -O https://github.com/yannickglt/alfred-devdocs/archive/v1.0.0.zip ; unzip -o v1.0.0.zip -d "$HOME/Library/Application Support/Alfred 2/Alfred.alfredpreferences/workflows/" ; rm -f v1.0.0.zip
```
