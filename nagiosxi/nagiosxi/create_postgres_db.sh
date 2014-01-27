#!/bin/sh

psql -c "create user nagiosxi with password 'n@gweb';"
psql -c "create database nagiosxi owner nagiosxi;"
