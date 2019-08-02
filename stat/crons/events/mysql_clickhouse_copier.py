import pymysql
from clickhouse_driver.client import Client
from pymysql.cursors import DictCursor
from contextlib import closing
import time

# mysqlCursor, clickhouseClient
def copy(mysqlCursor, clickhouse): 
   mysqlCursor.execute('SELECT max(id) mid FROM important_events')

   mysqlMaxId = mysqlCursor.fetchone()['mid']
   clickhouseMaxId = clickhouse.execute("SELECT max(id) mid FROM  mcn.important_events")[0][0]

   print("Copy new bulk of data mysqlMaxId: %d, clickhouseMaxId: %d" % (mysqlMaxId, clickhouseMaxId))

   if mysqlMaxId <= clickhouseMaxId :
       return False
   
   mysqlCursor.execute("SELECT id, date, client_id, event, source_id, from_ip, comment, context from important_events where id > %d  order by id limit 300000" % clickhouseMaxId)
   data = [ row for row in mysqlCursor]

   # ip address conversion
   for row in data:
       if row['from_ip'] is None:
           continue
       tetrads = [ str(x) for x in row['from_ip'] ]
       row['from_ip'] = '.'.join(tetrads)
   #print(data)

   clickhouse.execute("INSERT INTO mcn.important_events (id, date, client_id, event, source_id, from_ip, comment, context) VALUES", data, types_check=True)

   return True

with closing(pymysql.connect(
               host = "stat.mcn.ru",
               user= "stat_readonly",
               password="826ff19bc5e182",
               db="nispd",
               cursorclass=DictCursor)) as conn:
    with conn.cursor() as cursor:
        client = Client(host="eridanus3.mcn.ru", password="Sdfwe342") 
        while copy(cursor, client):
            pass
        client.disconnect()

        
