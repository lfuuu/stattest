#!/bin/bash

whDir="../web/export/health";

function monitorItem {
   ./$1.sh > $whDir/$1.json.tmp;
   mv -f $whDir/$1.json.tmp $whDir/$1.json
}

monitorItem loadAverage;
monitorItem apiFreeNumbers495;
monitorItem apiFreeNumbers499;
monitorItem apiFreeNumbersSilver;
monitorItem apiFreeNumbersAccount;
monitorItem ubillerLog;
monitorItem nnpPortedNumberLog;
monitorItem mttLog;
monitorItem mttProcess;
monitorItem tele2Log;
monitorItem numberPortedLog;
monitorItem tele2Process;
monitorItem nnpPortedApi;
monitorItem socketApi;
monitorItem uuApiLog;
monitorItem mailerLog;
monitorItem mailerProcess;
monitorItem handlerProcess;
monitorItem exportFreeNumbers;
monitorItem mncPortedLog;