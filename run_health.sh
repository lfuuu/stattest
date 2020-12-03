#!/bin/sh

while true; echo '----- START'; do php yii health; echo '> sleep for 60 sec'; sleep 60; done;