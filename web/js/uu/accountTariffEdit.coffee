class AccountTariffEdit

  country: null
  ndcType: null
  city: null
  didGroup: null
  operatorAccount: null

  numbersList: null
  numbersListSelectAll: null
  numbersListSelectAllCheckbox: null

  numbersListClass: null
  numbersListOrderByField: null
  numbersListOrderByType: null
  numbersListMask: null
  numbersListLimit: null

  tariffDiv: null
  tariffPeriod: null

  voipServiceTypeIdVal: null
  currencyVal: null
  isIncludeVat: null
  organizationId: null

  errorClassName: 'alert-danger'
  successClassName: 'alert-success'

# инициализация
  constructor: () ->
    setTimeout () =>
      @country = $('#voipCountryId').on('change', @onCountryChange)
      @city = $('#voipRegions').on('change', @onCityChange)
      @ndcType = $('#voipNdcType').on('change', @onNdcTypeChange)
      @didGroup = $('#voipDidGroup').on('change', @onDidGroupChange)
      @operatorAccount = $('#voipOperatorAccount').on('change', @showNumbersList)

      @numbersList = $('#voipNumbersList').on('change', 'input', @showTariffDiv)
      @numbersListSelectAll = $('#voipNumbersListSelectAll')
      @numbersListSelectAllCheckbox = @numbersListSelectAll.find('input').on('change', @selectAllNumbers)
      @tariffDiv = $('#voipTariffDiv')

      @numbersListClass = $('#voipNumbersListClass').on('change', @showNumbersList)
      @numbersListOrderByField = $('#voipNumbersListOrderByField').on('change', @showNumbersList)
      @numbersListOrderByType = $('#voipNumbersListOrderByType').on('change', @showNumbersList)
      @numbersListMask = $('#voipNumbersListMask').on('change', @showNumbersList)
      @numbersListLimit = $('#voipNumbersListLimit').on('change', @showNumbersList)

      @tariffPeriod = $('.accountTariffTariffPeriod').on('change', @onTariffPeriodChange)

      @voipServiceTypeIdVal = $('#voipServiceTypeId').val()
      @currencyVal = $('#voipCurrency').val()
      @isIncludeVat = $('#isIncludeVat').val()
      @organizationId = $('#organizationId').val()

      # Дополнительное поле "Статус склада мобильных номеров"
      @warehouseField = $('#voipNumbersWarehouseStatusField')
      @warehouseStatus = @warehouseField.find('#voipNumbersWarehouseStatus')

      $('#addAccountTariffVoipForm').on('submit', @onFormSubmit)

      # показать список номеров и обновить тариф
      @onNdcTypeChange()

    , 200 # Потому что select2 рендерит чуть позже. @todo

# при изменении страны
  onCountryChange: () =>
    countryVal = @country.val()
    if countryVal
# обновить список городов в зависимости от страны
      $.get '/uu/voip/get-cities', {countryId: countryVal, isWithEmpty: 1, format: 'options'}, (html) =>
        @city.html(html) # обновить значения
        @city.val('').trigger('change')

      @country.parent().parent().removeClass(@errorClassName)
      @city.prop('disabled', false)

    else
      @city.prop('disabled', true)
      @city.val('').trigger('change')
      @country.parent().parent().addClass(@errorClassName)

# при изменении города
# город в качестве доп. фильтра. Но можно и без него, тогда всё остальное (дид-группа, тариф и пр.) только относящиеся ко всей стране
  onCityChange: =>
    cityId = @city.val()

    # обновить список типов NDC в зависимости от города. Фактически geo/mob или freephone. Страна не важна
    $.get '/uu/voip/get-ndc-types', {isCityDepended: (if cityId then 1 else 0), isWithEmpty: 1, format: 'options'}, (html) =>
      @ndcType.html(html) # обновить значения
      @ndcType.val('').trigger('change')

# при изменении типа NDC
  onNdcTypeChange: =>
    countryId = @country.val()
    cityId = @city.val()
    ndcTypeId = @ndcType.val()

    # поведение поля "Статус склада мобильных номеров"
    @mobileDynamicBehavior(@warehouseStatus, @warehouseField)

    if ndcTypeId
      @ndcType.parent().parent().removeClass(@errorClassName)
    else
      @ndcType.parent().parent().addClass(@errorClassName)

    if countryId
      $.get '/uu/voip/get-did-groups', {countryId: countryId, cityId: (if cityId then cityId else -1), ndcTypeId: ndcTypeId, isWithEmpty: 1, format: 'options'}, (html) =>
        @didGroup.html(html) # обновить значения
        @didGroup.prop('disabled', false)
        @didGroup.val('').trigger('change')

      $.get '/uu/voip/get-operator-accounts', {countryId: countryId, cityId: cityId, isWithEmpty: 1, format: 'options'}, (html) =>
        @operatorAccount.html(html) # обновить значения
        @operatorAccount.prop('disabled', false)
        @operatorAccount.val('').trigger('change')

    else
      @didGroup.prop('disabled', true)
      @didGroup.val('').trigger('change')

      @operatorAccount.prop('disabled', true)
      @operatorAccount.val('').trigger('change')

  # Динамическое поведение при мобильном типе NDC
  mobileDynamicBehavior: (target, envelope) =>
    if !@ndcType.val() || @ndcType.val() != '2'
      target.val('').trigger('change')
      envelope.css({display: 'none'})
    else
      envelope.css({display: 'block'})

# при изменении DID-группы
  onDidGroupChange: =>
    @reloadTariffList()
    @showNumbersList()

# показать номера
  showNumbersList: =>
    ndcTypeId = @ndcType.val()
    countryId = @country.val()
    cityId = @city.val()
    didGroupId = @didGroup.val()

    if !ndcTypeId
      @showHideTariffDiv('')
      return

    $.get '/uu/voip/get-free-numbers', {
      countryId: countryId
      cityId: cityId
      didGroupId: didGroupId
      operatorAccountId: @operatorAccount.val()
      rowClass: @numbersListClass.val()
      orderByField: @numbersListOrderByField.val()
      orderByType: @numbersListOrderByType.val()
      mask: @numbersListMask.val()
      limit: @numbersListLimit.val()
      ndcTypeId: ndcTypeId
    }, (html) =>
      @showHideTariffDiv(html)

# выбрать все / снять выделение
  selectAllNumbers: =>
    isChecked = @numbersListSelectAllCheckbox.is(':checked')
    @numbersList.find('input').prop('checked', isChecked)
    @showTariffDiv()

  showHideTariffDiv: (html) =>
    @numbersList.html(html) # обновить значения
    if @numbersList.find('input').length > 1 # есть чекбоксы - показать 'выбрать все'
      @numbersListSelectAll.show()
    else
      @numbersListSelectAll.hide()
    @showTariffDiv()

# показать/скрыть выбор тарифа
  showTariffDiv: =>
    @tariffPeriod.trigger('change')
    if @numbersList.find('input:checked').length
      @numbersList.removeClass(@errorClassName)
      @tariffDiv.slideDown()
    else
      if @numbersList.html()
        @numbersList.addClass(@errorClassName)
      else
        @numbersList.removeClass(@errorClassName)
      @tariffDiv.slideUp()

# при изменении тарифа
  onTariffPeriodChange: =>
    if @tariffPeriod.val()
      @tariffPeriod.parent().parent().removeClass(@errorClassName)
    else
      @tariffPeriod.parent().parent().addClass(@errorClassName)

# перегрузить список тарифов
  reloadTariffList: =>
    countryId = @country.val()
    cityId = @city.val()
    ndcTypeId = @ndcType.val()

    if !ndcTypeId
      return

    $.get '/uu/voip/get-tariff-periods', {
        serviceTypeId: @voipServiceTypeIdVal,
        currency: @currencyVal,
        countryId: countryId,
        cityId: cityId,
        ndcTypeId: ndcTypeId,
        isWithEmpty: 1,
        format: 'options',
        isIncludeVat: @isIncludeVat,
        organizationId: @organizationId
      }, (html) =>
      @tariffPeriod.val('').html(html) # обновить значения
      @tariffPeriod.trigger('change')

# при сабмите формы
  onFormSubmit: (e) =>
# чтобы раньше времени не сабмитить, когда юзер нажимает enter в фильтре
    if not @tariffPeriod.val()
      e.stopPropagation()
      e.preventDefault()

new AccountTariffEdit()