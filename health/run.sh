#!/bin/bash

./loadAverage.sh > ../web/export/health/loadAverage.json
./apiFreeNumbers495.sh > ../web/export/health/apiFreeNumbers495.json
./apiFreeNumbers499.sh > ../web/export/health/apiFreeNumbers499.json
./apiFreeNumbersSilver.sh > ../web/export/health/apiFreeNumbersSilver.json
./apiFreeNumbersAccount.sh > ../web/export/health/apiFreeNumbersAccount.json
./ubillerLog.sh > ../web/export/health/ubillerLog.json
./mttLog.sh > ../web/export/health/mttLog.json
./mttProcess.sh > ../web/export/health/mttProcess.json
./nnpPortedApi.sh > ../web/export/health/nnpPortedApi.json
./socketApi.sh > ../web/export/health/socketApi.json
./uuApiLog.sh > ../web/export/health/uuApiLog.json
./mailerLog.sh > ../web/export/health/mailerLog.json
./mailerProcess.sh > ../web/export/health/mailerProcess.json
./handlerProcess.sh > ../web/export/health/handlerProcess.json
./exportFreeNumbers.sh > ../web/export/health/exportFreeNumbers.json
