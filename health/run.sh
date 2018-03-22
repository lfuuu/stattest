#!/bin/bash

./loadAverage.sh > ../web/health/loadAverage.json
./apiFreeNumbers495.sh > ../web/health/apiFreeNumbers495.json
./apiFreeNumbers499.sh > ../web/health/apiFreeNumbers499.json
./apiFreeNumbersSilver.sh > ../web/health/apiFreeNumbersSilver.json
./apiFreeNumbersAccount.sh > ../web/health/apiFreeNumbersAccount.json
./ubillerLog.sh > ../web/health/ubillerLog.json
./mttLog.sh > ../web/health/mttLog.json
./mttProcess.sh > ../web/health/mttProcess.json
./nnpPortedApi.sh > ../web/health/nnpPortedApi.json
./socketApi.sh > ../web/health/socketApi.json
./uuApiLog.sh > ../web/health/uuApiLog.json
./mailerLog.sh > ../web/health/mailerLog.json
./mailerProcess.sh > ../web/health/mailerProcess.json
./handlerProcess.sh > ../web/health/handlerProcess.json
./exportFreeNumbers.sh > ../web/health/exportFreeNumbers.json
