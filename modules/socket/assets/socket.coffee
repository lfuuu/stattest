class SocketWebClient

  # разрешены ли нотификации
  NOTIFICATION_PERMISSION_GRANTED: 'granted' # да
  NOTIFICATION_PERMISSION_DENIED: 'denied' # нет
  NOTIFICATION_PERMISSION_DEFAULT: 'default' # по запросу

# конструктор
  constructor: () ->
    if not window.io or not window.ioUrl
# сокет-сервер упал
      return


    # разрешены ли нотификации
    @notificationPermission = if Notification then Notification.permission.toLowerCase() else @NOTIFICATION_PERMISSION_DENIED
    if @notificationPermission == @NOTIFICATION_PERMISSION_DEFAULT
# "по запросу" - запросить разрешение на уведомление
      Notification.requestPermission() # https://habrahabr.ru/post/183630/

    # @link https://notifyjs.com/
    # Но ссылки нельзя указывать :-(
    # $.notify('Комментарий добавлен', {className: 'success', autoHideDelay: 3000});

    # отрендерить иконку
    @renderIcon()

    # соединиться с сокет-сервером
    @connect()

# удалить теги
  stripTags: (html) ->
    tmp = document.createElement("div")
    tmp.innerHTML = html
    text = tmp.textContent || tmp.innerText || ""
    text.replace(/\n/g, '<br/>') # nl2br

# отрендерить иконку
  renderIcon: =>
    @socketDiv = $('<div>')
      .addClass('popover fade left in')
      .attr('role', 'tooltip')
      .attr('id', 'socket-div')
      .append($('<div>').addClass('arrow'))
      .hide()
      .appendTo($('body'))

    @socketImg = $('<span>')
      .addClass('glyphicon glyphicon-eye-close')
      .attr('aria-hidden', 'true')
      .attr('id', 'socket-img')
      .attr('title', 'Серверные уведомления')
    @socketImg.on('click', () =>
      @socketDiv.slideToggle()
    )
    @socketImg.appendTo($('#btn-options').parent())

# соединиться с сокет-сервером
  connect: () =>
    @socket = io(window.ioUrl);
    @socketOnConnect()
    @socketOnDisconnect()
    @socketOnMessage()
#    @socket.emit('message', {message: 'Hello, world!'})

# при установке соединения
  socketOnConnect: =>
    @socket.on('connect', =>
      @socketImg
        .removeClass('glyphicon-eye-close')
        .addClass('glyphicon-eye-open');
    )

# при разрыве соединения
  socketOnDisconnect: =>
    @socket.on('disconnect', =>
      @socketImg
        .removeClass('glyphicon-eye-open')
        .addClass('glyphicon-eye-close');
    )

# при получении события
  socketOnMessage: =>
    @socket.on('message', (json) =>
      title = $('<span>')
        .append(json['title'])

      message = $('<div>')
        .append(json['messageHtml'])

      # добавить отправителя
      message
        .prepend($('<b>').append(title))
        .prepend($('<span>').append(json['userFrom'] + ': '))

      # добавить дату
      date = new Date()
      dateHours = date.getHours()
      if dateHours < 10
        dateHours = '0' + dateHours
      dateMinutes = date.getMinutes()
      if dateMinutes < 10
        dateMinutes = '0' + dateMinutes
      message.prepend('[' + dateHours + ':' + dateMinutes + '] ')

      # добавить во всплывашку
      $newContent = $('<div>')
        .addClass('alert alert-' + (if json['type'] then json['type'] else 'warning') + ' alert-dismissible fade in')
        .attr('role', 'alert')
        .append('<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>')
        .append(message)

      # автоскрыть всплывашку
      if json['timeout']
        setTimeout(
          ->
            $newContent.find('button').click()
          json['timeout']
        )

      @socketDiv
        .append($newContent)
        .show()

      # кроме уведомления во вкладку браузера попытаемся сделать системное уведомление
      if @notificationPermission == @NOTIFICATION_PERMISSION_DEFAULT
# "по запросу" - обновить статус, ибо юзер уже мог разрешить или запретить
        @notificationPermission = if Notification then Notification.permission.toLowerCase() else @NOTIFICATION_PERMISSION_DENIED

      if @notificationPermission == @NOTIFICATION_PERMISSION_GRANTED and json['messageTxt'] and json['isNotification'] == 1
# "разрешено" - отправить уведомление
        console.log(json)
        notification = new Notification(json['title'],
#tag : '',
          body: json['messageTxt'],
          icon: '/images/logo2.gif'
        )

        # Обработчик клика
        if (json['url'])
          notification.onclick = ->
            window.open(json['url'])
    )

new SocketWebClient()