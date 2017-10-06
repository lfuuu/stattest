#!/bin/bash

./loadAverage.sh > ../web/operator/_private/healthLoadAverage.json
./apiFreeNumbers495.sh > ../web/operator/_private/healthApiFreeNumbers495.json
./apiFreeNumbers499.sh > ../web/operator/_private/healthApiFreeNumbers499.json
./apiFreeNumbersSilver.sh > ../web/operator/_private/healthApiFreeNumbersSilver.json
./apiFreeNumbersAccount.sh > ../web/operator/_private/healthApiFreeNumbersAccount.json
./ubiller.sh > ../web/operator/_private/healthUbiller.json
