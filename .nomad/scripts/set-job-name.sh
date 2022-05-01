#!/bin/bash

sed -i "/job/c\job \"$1\" {" apiary.nomad
