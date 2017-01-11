# Overview

H Tracker is a Hawk plugin that allow you to manage tasks, and sort them by project.




# Features

## Projects
* Create, update and delete projects


## Tasks
* Create / update / remove a task
* Choose the project the task is associated to
* Write a full description to your task with a rich editor
* Choose the priority of a task
* Update the status of a task
* Assign the tickets to any registered user
* Choose the deadline of the task
* See the history of the a task :
    * Any update on the task create a comment in the task history
    * Create your own comments on the task


## Plugin settings
* Manage the status you want to display for a task (defaultly 'open' and 'closed' status are automacially created when installing the plugin )


---


# Configure H Tracker
On the settings page of the plugin (accessible throw the plugins management page of your intance Hawk), you can setup the status of the tasks you want to let appeared.
This feature can be useful of you want to follow the advancement of the tasks in your team, to calculate your budgets, ...etc

To add a status, click on the button "Add". The new empty status will be created at the end of the list. You can change it name (as well for every existing status),
and change it position in the list, with the arrows at the left of the status name.

To remove an unused status, click on the trash button at the right of the status name.

# Dependencies
This plugin depends on the plugins :
* <a href="http://hawk-app.fr/#!/store/plugins/h-widgets" target="_blank">H Widgets</a>

# Author
This plugin is devloped by the company Elvyrra

©Elvyrra S.A.S

# Changeset

## v1.7.0
* Unable to remove the two first status (open and closed)
* Add a color code to display the over delayed tasks
* Change the status of a task directly from the tasks list