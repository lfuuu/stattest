#!/bin/ash

set -x

if [[ -z $1 ]]; then
  echo 'tumbprint not set'
  exit 1
fi

if [[ -z $2 ]]; then
  echo 'src file not set'
  exit 1
fi

if [[ ! -f $2 ]]; then
  echo 'src file not found'
  exit 1
fi

if [[ -z $3 ]]; then
  echo 'dst file not set'
  exit 1
fi

# MCN Telekom
# old 4fe6047a964d397c9a7c445e643dc20a0aa69be7
# new 883149876742672c00b0ddb1efa137a0b6a38e99

scp -P 33778 $2 adima@85.94.32.195:/tmp/
ssh -p 33778 adima@85.94.32.195 /opt/cprocsp/bin/amd64/cryptcp -sign -thumbprint $1 -der -strict -cadesbes -nochain -detached /tmp/${2##*/} /tmp/${2##*/}.sgn
scp -P 33778 adima@85.94.32.195:/tmp/${2##*/}.sgn $3
ssh -p 33778 adima@85.94.32.195 rm -f /tmp/${2##*/} /tmp/${2##*/}.sgn
chown www-data:www-data $3