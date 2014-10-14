#!/usr/bin/env python
# -*- coding: utf-8 -*-

__THREADS = 60	#количество потоков
__MAX_SQL_LEN = 800000 #максимальная длина sql запроса
__LOCKFILE = 'autoping.lock' #файл блокировок

__MAIL_FROM = 'MCN Info <info@mcn.ru>'
__MAIL_FROM_RAW = 'info@mcn.ru'
__MAIL_SMTP_SERV = 'localhost'
__MAIL_SMTP_PORT = 25

__DEBUG_MODE = False #если True, вместо insert'ов в базу будет вывод запросов
__DEBUG_SWAP_TABLES_MODE = False #если True, insert'ы пишутся в другую таблицу. Дополняет предыдущую директиву __DEBUG_MODE
__DEBUG_FORCE_MAIL = False #если True, письмо отправляется на email, даже если период отправления не настал
__DEBUG_MAIL = False #если True, письмо со спамом отправится на __DEBUG_ADDR вместо оригинального мейла, указанного в базе
__DEBUG_ADDR = 'dga@mcn.ru' #смотреть выше

import time
import threading
import Queue
import MySQLdb
import smtplib
import subprocess
import re
import fcntl
import os.path
import base64

v = {'host':'localhost','user':'stat_operator','passwd':'3616758a','db':'nispd','port':3306}
t = {'host':'127.0.0.1','user':'stat_operator','passwd':'3616758a','db':'nispd','port':3307}
c = v

connection = MySQLdb.connect(
	host=c['host'],
	user=c['user'],
	passwd=c['passwd'],
	db=c['db'],
	port=c['port']
)

ping_regex = re.compile("time=(\d+)(?:\.(\d+))? ms")
cidr_regex = re.compile("(\d+)\.(\d+)\.(\d+)\.(\d+)(?:\/(\d+))?")

def send_spam():
	"""Отправляет отчет по потерям на email, для клиент-email связки, указанной
	в monitor_clients"""

	smtp = smtplib.SMTP(__MAIL_SMTP_SERV,__MAIL_SMTP_PORT)

	cur = connection.cursor()
	cur.execute('''
		select
			mc.id,
			mc.client,
			mc.email,
			mc.allow_bad,
			mc.period_mail,
			mc.period_use
		from
			monitor_clients mc
		inner join
			monitor_ips mi
		ON
			mi.monitor_id = mc.id
		where
			mi.count > mc.allow_bad
		group by
			mc.id
	''')
	while True:
		row = cur.fetchone()
		if row == None:
			break
		nperiod = row[5]
		if __DEBUG_FORCE_MAIL or row[5] <= 0:
			msg = "From: %s\r\nTo: %s\r\n"
			msg += "Subject: =?utf-8?B?%s?=\r\n"%base64.b64encode('неудачный ping'.decode('koi8_r').encode('utf8'))
			msg += 'Content-Type: text/plain; charset = "koi8-r"\r\n'
			msg += "Content-Transfer-Encoding: 8bit\r\n"
			msg += "\r\n"
			msg += "Неудачные пинги у клиента %s (разрешено - %s):\n" % (row[1],row[3])# $r['client'], $r['allow_bad']
			msg += "IP \tколичество неудачных пингов\n"

			cur1 = connection.cursor()
			cur1.execute('''
				select
					inet_ntoa(ip_int),
					`count`
				from
					monitor_ips
				where
					monitor_id=%i
			''' % row[0])
			while True:
				row1 = cur1.fetchone()
				if row1 == None:
					break
				msg += "%s\t%s\n" % (str(row1[0]),str(row1[1]))
			cur1.close()
			maddrs = row[2].split(';') if not __DEBUG_MAIL else __DEBUG_ADDR.split(',')

			smtp.sendmail(__MAIL_FROM_RAW,maddrs,msg%(__MAIL_FROM,','.join(maddrs)))

			print "Mail sent on ", ','.join(maddrs)
		else:
			nperiod -= 1

		q = '''
			update
				monitor_clients
			set
				period_use = %i
			where
				id = %i
		''' % (nperiod,row[0])
		if not __DEBUG_MODE:
			cur.execute(q)
		else:
			print q
	cur.close()
	smtp.quit()

def get_flock(curtime):
	"""Читает файл блокировки. Если другой скрипт уже пингует полученную пятиминутку - возвращает False,
	иначе записывает в файл идентификатор своей пятиминутки, сохраняя остальные идентификаторы файла."""
	opmode = 'a+' if os.path.exists(__LOCKFILE) else 'w+'
	fl = False
	f = open(__LOCKFILE,opmode)
	fcntl.lockf(f,fcntl.LOCK_EX)
	f.seek(0)
	works = f.read().split(',')
	if not str(curtime) in [str(i) for i in works if works]:
		f.seek(0)
		f.truncate(0)
		works.append(curtime)
		f.write(','.join([str(i) for i in works if i]))
		f.flush()
		fl = True
	fcntl.lockf(f,fcntl.LOCK_UN)
	f.close()
	return fl

def release_flock(curtime):
	"""Открывает файл блокировки, вычищает из него свой идентификатор пятиминутки, оставляя
	чужие идентификаторы нетронутыми."""
	f = open(__LOCKFILE, 'a+')
	fcntl.lockf(f,fcntl.LOCK_EX)
	f.seek(0)
	works = f.read().split(',')
	works_buf = []
	for i in [str(n) for n in works if n]:
		if int(i) != curtime:
			works_buf.append(i)
	f.seek(0)
	f.truncate(0)
	f.write(','.join([str(i) for i in works_buf if i]))
	f.flush()
	fcntl.lockf(f,fcntl.LOCK_UN)
	f.close()

def set_result():
	"""Читает глобальный(для модуля) список Stats, в котором должны находиться данные
	пропинговки. Составляет из данных, и возвращает sql запрос для вставки данных в базу."""
	sql_begin = 'insert into monitor_5min (ip_int,time300,value) values '
	if __DEBUG_SWAP_TABLES_MODE:
		sql_begin = 'insert into monitor_5min_test (ip_int,time300,value) values '
	ins = "(inet_aton('%s'),%s,%s),"
	sql = ''
	ret = []

	for p in Stats:
		if not len(sql):
			sql = sql_begin + ins % (str(p[0]),str(p[1]),str(p[2]))
			if len(sql)-1 > __MAX_SQL_LEN:
				raise "Don't have enough space for sql query..."
			else:
				continue
		if len(sql + ins % (str(p[0]),str(p[1]),str(p[2])))-1 > __MAX_SQL_LEN:
			ret.append(sql[0:-1])
			sql = sql_begin + ins % (str(p[0]),str(p[1]),str(p[2]))
			continue
		sql += ins % (str(p[0]),str(p[1]),str(p[2]))
	ret.append(sql[0:-1])

	for i in IPs_bad.keys():
		if IPs_bad[i][2]:
			if __DEBUG_SWAP_TABLES_MODE:
				print '''
					insert into
						monitor_ips
					set
						ip_int = inet_aton('%s'),
						monitor_id = %i,
						`count` = %i
					on duplicate key update
						`count` = values(`count`)
				''' % (i,IPs_bad[i][0],IPs_bad[i][1])
			else:
				ret.append('''
					insert into
						monitor_ips
					set
						ip_int = inet_aton('%s'),
						monitor_id = %i,
						`count` = %i
					on duplicate key update
						`count` = values(`count`)
				''' % (i,IPs_bad[i][0],IPs_bad[i][1]))

	return ret


def get_source():
	"""Читает из базы ip адресы, которые необходимо пропинговать, и возвращает список
	кортежей вида (список ip адресов, идентификатор клиента). Так же заполняет
	глобальные(для модуля) списки IPs_bad("плохие" ip, на которые были обнаружены потери сегодня)
	и mon_cli(список клиентов, за которыми ведется мониторинг потерь, с отправкой на email)"""
	nets = []
	cur = connection.cursor()

	cur.execute('''
		select
			trim(ip),
			client
		from
			usage_welltime
		where
			actual_from <= NOW()
		and
			actual_to > NOW()
		and
			ip != ""
	''')
	row = True
	while row:
		row = cur.fetchone()
		if row == None:
			break
		if not row[0]:
			continue
		nets.append((from_cidr(row[0]),row[1]))


	cur.execute('''
		select
			trim(usage_ip_routes.net),
			usage_ip_ports.client
		from
			usage_ip_routes
		INNER JOIN
			usage_ip_ports
		ON
			usage_ip_ports.id = usage_ip_routes.port_id
		where
			usage_ip_routes.actual_from <= NOW()
		and
			usage_ip_routes.actual_to > NOW()
	''')
	row = True
	while row:
		row = cur.fetchone()
		if row == None:
			break
		if not row[0]:
			continue
		nets.append((from_cidr(row[0]),None))

	cur.execute('''
		select
			trim(ip),
			client
		from
			usage_ip_ppp
		where
			actual_from <= NOW()
		and
			actual_to >= NOW()
	''')
	row = True
	while row:
		row = cur.fetchone()
		if row == None:
			break
		if not row[0]:
			continue
		nets.append((from_cidr(row[0]),None))

	cur.execute('''
		select
			trim(ip),
			client
		from
			tech_cpe
		where
			actual_from <= NOW()
		and
			actual_to >= NOW()
		and
			ip != ""
	''')
	row = True
	while row:
		row = cur.fetchone()
		if row == None:
			break
		if not row[0]:
			continue
		nets.append((from_cidr(row[0]),row[1]))

	cur.execute('''
		select
			trim(net),
			router
		from
			tech_routers
		where
			actual_from <= NOW()
		and
			actual_to >= NOW()
	''')
	row = True
	while row:
		row = cur.fetchone()
		if row == None:
			break
		if not row[0]:
			continue
		nets.append((from_cidr(row[0]),'mcn'+str(row[1])))

	cur.execute('select inet_ntoa(ip_int), monitor_id, `count` from monitor_ips')
	global IPs_bad
	while True:
		row = cur.fetchone()
		if row == None:
			break
		IPs_bad[row[0]] = [row[1],row[2],False]

	cur.execute('select id,client from monitor_clients')
	global mon_cli
	while True:
		row = cur.fetchone()
		if row == None:
			break
		mon_cli[row[1]] = row[0]

	cur.close()

	ret = {}
	for i in nets:
		for j in i[0]:
			ret[j] = i[1]

	return ret

def from_cidr(addr):
	"""Получает cidr адрес, либо просто ip. Возвращает список либо первых 2 из подсети,
	либо с одним ip адресом, который был получен"""
	m = cidr_regex.match(addr)
	if not m:
		return []
	m = m.groups()
	if not m[4] or int(m[4])==32:
		return ["%i.%i.%i.%i" % (int(m[0]),int(m[1]),int(m[2]),int(m[3]))]
	else:
		return [
			"%i.%i.%i.%i" % (int(m[0]),int(m[1]),int(m[2]),int(m[3])+1),
			"%i.%i.%i.%i" % (int(m[0]),int(m[1]),int(m[2]),int(m[3])+2)
		]

def agg_1h(curtime):
	"""Аггрегирует статистику по пятиминуткам(5min), в статистику по часам(1h), подчищая таблицы
	monitor_5min и monitor_ips. Выставляет всем клиентам флаг period_use в 0(таблица monitor_clients)"""
	cur = connection.cursor()
	cur.execute('start transaction')
	cur.execute('''
		insert into
			monitor_1h
		select
			ip_int,
			FLOOR(time300/12) as time3600,
			sum(IF(value=0,1,0)) + 12 - count(*) as bad_count,
			sum(value) as good_sum
		FROM
			monitor_5min
		where
			ip_int>0
		and
			time300<%s
		GROUP BY
			ip_int,
			time3600
	''' % (curtime-288))

	cur.execute('''
		delete from
			monitor_5min
		where
			ip_int > 0
		and
			time300 < %s
	''' % (curtime-288))

	cur.execute('truncate table monitor_ips')
	cur.execute('update monitor_clients set period_use=0')

	cur.execute('commit')
	cur.close()

def check_curtime():
	"""Проверяет, не собрана ли уже статистика на текущую пятиминутку. Если уже собрана,
	возвращает 0, иначе возвращает идентификатор текущей пятиминутки."""
	cur = connection.cursor()
	sql = """
		select
			max(time300) as tmax,
			floor(unix_timestamp()/300) as tcur
		from
			monitor_5min
		where
			ip_int!=0
	"""
	cur.execute(sql)
	row = cur.fetchone()
	cur.close()
	if row[0] < row[1]:
		return row[1]
	else:
		return 0

def ping_addr(addr):
	"""Отправляет пинг на полученный ip адрес, возвращает распарсенный результат в виде числа.
	Если прошла потеря, возвращает -1"""
	msg = subprocess.Popen("ping -s 32 -c 1 -w 1 %s 2> /dev/null" % str(addr),shell=True,stdout=subprocess.PIPE).stdout.read()
	m = ping_regex.findall(msg)
	if len(m) and len(m[0]):
		return int(m[0][0])
	return -1

def pinger_daemon(idx, nowtime, strtime):
	"""Демон, ожидающий очереди ip адреса. Получает его, пингует, пишет по нему статистику, и ждет
	очереди дальше."""
	global IPs_bad
	global threads_processing_cnt
	print "Starting Thread #%i"%idx
	while True:
		item = Q.get()

		pingtime = ping_addr(item[0])
		if pingtime < 0:
			pingtime = 0
		elif pingtime == 0:
			pingtime = 1

		if item[0] in IPs_bad.keys():
			if not pingtime:
				IPs_bad[item[0]][1] += 1
				IPs_bad[item[0]][2] = True
		elif not pingtime and mon_cli.get(item[1],False):
			IPs_bad[item[0]] = [mon_cli[item[1]],1,True]
		
		global Stats
		Stats.append([item[0],nowtime,pingtime])

		threads_processing_cnt[idx] += 1
		Q.task_done()

Q = Queue.Queue()
Stats = []
IPs_bad = {}
mon_cli = {}
threads_processing_cnt = []

def run_main():
	"""Главный поток. Получает блокировку, запускает дочерние потоки, генерирует
	для них очередь адресов, ждет выполнения, пишет в базу результат"""
	curtime = check_curtime()
	if not curtime:
		print "already processed"
		return 0

	cnt_ = 0

	if get_flock(curtime):
		if curtime % 288 == 240 and not __DEBUG_MODE: # ну бред же полный... не проще ли проверять по strftime("%H")==20? проще и нагляднее. но пока оставлю "шепикоходконем" в оригинале
			agg_1h(curtime)
		for i in range(__THREADS):
			t = threading.Thread(target=pinger_daemon,name="Thread #%i"%i,args=(i,curtime,time.strftime("%Y-%m-%d %H:%M:%S")))
			t.setDaemon(True)
			t.start()
			threads_processing_cnt.append(0)
		vals = get_source()
		for ipaddr in vals.keys():
			Q.put((ipaddr,vals[ipaddr]))
			cnt_+=1
		Q.join()

		res = set_result()
		cur = connection.cursor()
		cur.execute('start transaction')
		for i in res:
			if (__DEBUG_MODE and __DEBUG_SWAP_TABLES_MODE) or not __DEBUG_MODE:
				cur.execute(i)
			else:
				print i
		cur.execute('commit')
		cur.close()

		if (__DEBUG_MODE and __DEBUG_MAIL) or not __DEBUG_MODE:
			send_spam()

		release_flock(curtime)
	return cnt_

if __name__ == "__main__":
	print "BEGIN;#######################", time.strftime("%Y-%m-%d %H:%M:%S"), "#######################"
	cnt_ = run_main()
	for i in range(len(threads_processing_cnt)):
		print "Processed of Thread #%i - %i" % (i,threads_processing_cnt[i])
	print "Total Processed:", cnt_
	print "END;#########################", time.strftime("%Y-%m-%d %H:%M:%S"), "#######################\n\n"
