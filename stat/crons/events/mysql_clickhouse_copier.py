import pymysql
from clickhouse_driver.client import Client
from pymysql.cursors import DictCursor
from contextlib import closing
import time
import requests
import simplejson as json
import sys

# mysqlCursor, clickhouseClient
def copy(mysqlCursor, clickhouse):
   mysqlCursor.execute('SELECT max(id) mid FROM important_events')

   mysqlMaxId = mysqlCursor.fetchone()['mid']
   clickhouseMaxId = clickhouse.execute("SELECT max(id) mid FROM  mcn.important_events")[0][0]

   print("important_events: Copy new bulk of data mysqlMaxId: %d, clickhouseMaxId: %d" % (mysqlMaxId, clickhouseMaxId))

   if mysqlMaxId <= clickhouseMaxId :
       return False

   mysqlCursor.execute("SELECT id, date, client_id, event, source_id, from_ip, comment, context from important_events where id > %d  order by id limit 1000" % clickhouseMaxId)
   data = [ row for row in mysqlCursor]

   # ip address conversion
   for row in data:
       if row['from_ip'] is None:
          row['from_ip'] = ''
       else:
          tetrads = [ str(ord(x)) for x in row['from_ip'] ]
          row['from_ip'] = '.'.join(tetrads)

   #print(data)

   clickhouse.execute("INSERT INTO mcn.important_events (id, date, client_id, event, source_id, from_ip, comment, context) VALUES", data, types_check=True)

   for row in data:
       row['date'] = row['date'].isoformat(' ')
       requests.post("http://tiberis.mcn.ru:8888/stat", data={'json':json.dumps(row)})

   return True

def copyUu(mysqlCursor, clickhouse):
   mysqlCursor.execute('SELECT max(id) as mid FROM uu_account_log_resource ulr WHERE price != 0')

   mysqlMaxId = mysqlCursor.fetchone()['mid']
   clickhouseMaxId = clickhouse.execute("SELECT max(id) mid FROM  mcn.uu_account_log_resource")[0][0]

   print("uu_account_log_resource: Copy new bulk of data mysqlMaxId: %d, clickhouseMaxId: %d" % (mysqlMaxId, clickhouseMaxId))

   if mysqlMaxId <= clickhouseMaxId :
       return False

   mysqlCursor.execute("""
    select c.id as account_id, ulr.id, ulr.date_from, ulr.date_to, ulr.account_tariff_id, ulr.price, utr.resource_id
    from uu_account_log_resource ulr
    inner JOIN uu_account_tariff uat ON uat.id = ulr.account_tariff_id
    inner JOIN clients c ON c.id = uat.client_account_id
    inner JOIN uu_tariff_resource utr ON utr.id = ulr.tariff_resource_id
    WHERE
    ulr.price != 0 and ulr.id > %d
    order by ulr.id
    limit 100000""" % clickhouseMaxId)
   data = [ row for row in mysqlCursor]

   for row in data:
       row['price'] = float(row['price'])

   clickhouse.execute("INSERT INTO mcn.uu_account_log_resource (account_id, id, date_from, date_to, account_tariff_id, price, resource_id) VALUES", data, types_check=True)

   return True

with closing(pymysql.connect(
               host = "tiberis.mcn.ru",
               user= "stat_readonly",
               password=sys.argv[1],
               db="nispd",
               cursorclass=DictCursor)) as conn:
    with conn.cursor() as cursor:
        client = Client(host="eridanus3.mcn.ru", password=sys.argv[2])
        while copy(cursor, client):
            pass

        while copyUu(cursor, client):
            pass

        client.disconnect()

