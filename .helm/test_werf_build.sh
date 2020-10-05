#!/bin/bash

. $(multiwerf use 1.1 stable --as-file)

#werf build --dir ../ --stages-storage :local --introspect-error=true --log-debug=true --log-verbose=true
werf build --dir ../ --stages-storage :local --introspect-error=true
