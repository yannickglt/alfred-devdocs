alfred-devdocs
==============

Alfred workflow for the amazing [devdocs.io](http://devdocs.io/) documentations.

# Install
Use the packaged workflow [DevDocs.alfredworkflow](https://github.com/packal/repository/raw/master/com.yannickglt.alfred2.devdocs/devdocs.alfredworkflow) from packal.

## Add docs
Now add some documentations to your workflow like this:

```
docs:add javascript
```

# How to

## Add a documentation

By default, the Alfred Devdocs workflow comes without any documentation. First of all, you must add one or several docs you want to browse using the `cdoc:add` command. For example:
```
cdoc:add javascript
```

#### Other configuration commands

- `cdoc:list`: List all docs you can add in your workflow
- `cdoc:add`: Add a doc in your workflow, if you have already all docs, that command do nothing
- `cdoc:remove`: Remove a doc in your workflow, if you haven't a doc in your workflow, that command do nothing
- `cdoc:all`: Add all docs available to your workflow (not recommended)
- `cdoc:nuke`: Remove all docs in your workflow
- `cdoc:refresh`: Refresh cache for a doc (if specified) or all the added docs otherwise
- `cdoc:alias`: Create an alias for a documentation
- `cdoc:unalias`: Remove an existing alias of a documentation

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

## Use behind an HTTP proxy
Define the `HTTP_PROXY` environment variable in Alfred as below. You can define the `HTTP_PROXY_AUTHORIZATION` as well if your proxy needs basic authentication.
![image](https://cloud.githubusercontent.com/assets/1006426/25639687/14c63202-2f8d-11e7-8384-a75ba0b7059d.png)
