#!/usr/bin/env bash
IS_PRODUCTION=$1

if cd frontend; then
    git pull origin master
else
    git clone https://github.com/Listen-Online/lion-frontend.git frontend
fi

npm install
npm run build-dependency

if [[ $IS_PRODUCTION == "prod" || $IS_PRODUCTION == "production" ]]; then
    npm run build
    cp -r ./build ../
else
    npm run start
fi