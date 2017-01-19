class SocketWebClient

  # конструктор
  constructor: () ->
    if not window.io or not window.ioUrl
# сокет-сервер упал
      return

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
      message = json['message']
      message = @stripTags(message) # защита от js-инъекций

      if (json['url'])
# добавить ссылку
        message = $('<a>')
          .attr('href', json['url'])
          .append(message)

      message = $('<span>').append(message)

      # добавить отправителя
      message.prepend($('<b>').append(json['userFrom'] + ': '))

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

      @socketDiv
        .append($newContent)
        .show()
    )

new SocketWebClient()