#!/bin/bash

./loadAverage.sh > ../web/operator/_private/healthLoadAverage.json
./apiFreeNumbers495.sh > ../web/operator/_private/healthApiFreeNumbers495.json
./apiFreeNumbers499.sh > ../web/operator/_private/healthApiFreeNumbers499.json
./apiFreeNumbersSilver.sh > ../web/operator/_private/healthApiFreeNumbersSilver.json
./apiFreeNumbersAccount.sh > ../web/operator/_private/healthApiFreeNumbersAccount.json
./ubiller.sh > ../web/operator/_private/healthUbillerLog.json
./mttLog.sh > ../web/operator/_private/healthMttLog.json
./mttProcess.sh > ../web/operator/_private/healthMttProcess.json
./nnpPortedApi.sh > ../web/operator/_private/healthNnpPortedApi.json
./socketApi.sh > ../web/operator/_private/healthSocketApi.json
./uuApiLog.sh > ../web/operator/_private/healthUuApiLog.json
./mailerLog.sh > ../web/operator/_private/healthMailerLog.json
./mailerProcess.sh > ../web/operator/_private/healthMailerProcess.json
./handlerProcess.sh > ../web/operator/_private/healthHandlerProcess.json
