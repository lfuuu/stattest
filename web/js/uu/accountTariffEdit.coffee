class AccountTariffEdit

  country: null
  ndcType: null
  city: null
  didGroup: null

  numbersList: null
  numbersListSelectAll: null
  numbersListSelectAllCheckbox: null
  numbersListFilter: null

  numbersListClass: null
  numbersListOrderByField: null
  numbersListOrderByType: null
  numbersListMask: null
  numbersListLimit: null

  tariffDiv: null
  tariffPeriod: null

  voipServiceTypeIdVal: null
  currencyVal: null
  isPostpaid: null

  errorClassName: 'alert-danger'
  successClassName: 'alert-success'

# инициализация
  constructor: () ->
    setTimeout () =>
      @country = $('#voipCountryId').on('change', @onCountryChange)
      @ndcType = $('#voipNdcType').on('change', @onCityChange)
      @city = $('#voipRegions').on('change', @onCityChange)
      @didGroup = $('#voipDidGroup').on('change', @showNumbersList)

      @numbersList = $('#voipNumbersList').on('change', 'input', @showTariffDiv)
      @numbersListSelectAll = $('#voipNumbersListSelectAll')
      @numbersListSelectAllCheckbox = @numbersListSelectAll.find('input').on('change', @selectAllNumbers)
      @numbersListFilter = $('#voipNumbersListFilter')
      @tariffDiv = $('#voipTariffDiv')

      @numbersListClass = $('#voipNumbersListClass').on('change', @showNumbersList)
      @numbersListOrderByField = $('#voipNumbersListOrderByField').on('change', @showNumbersList)
      @numbersListOrderByType = $('#voipNumbersListOrderByType').on('change', @showNumbersList)
      @numbersListMask = $('#voipNumbersListMask').on('change', @showNumbersList)
      @numbersListLimit = $('#voipNumbersListLimit').on('change', @showNumbersList)

      @tariffPeriod = $('.accountTariffTariffPeriod').on('change', @onTariffPeriodChange)

      @voipServiceTypeIdVal = $('#voipServiceTypeId').val()
      @currencyVal = $('#voipCurrency').val()
      @isPostpaid = $('#isPostpaid').val()

      $('#addAccountTariffVoipForm').on('submit', @onFormSubmit)

      @initCountry(false)

    , 200 # Потому что select2 рендерит чуть позже. @todo

# при изменении страны
  onCountryChange: () =>
    @initCountry(true)

# при изменении страны
  initCountry: (isUpdateCities) =>
    countryVal = @country.val()
    if countryVal

      if isUpdateCities
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

# при изменении типа NDC или города
  onCityChange: =>
    cityId = @city.val()
    ndcTypeId = @ndcType.val()

    # пометить себя красным, если можно выбирать, но не выбран
    if @city.prop('disabled') or cityId
      @city.parent().parent().removeClass(@errorClassName)
    else
      @city.parent().parent().addClass(@errorClassName)

    if cityId
# заранее подготовить список тарифов и пакетов
      @reloadTariffList()

    if cityId && ndcTypeId >= 0
      $.get '/uu/voip/get-did-groups', {cityId: cityId, isWithEmpty: 1, format: 'options'}, (html) =>
        @didGroup.html(html) # обновить значения
        @didGroup.prop('disabled', false)
        @didGroup.val('').trigger('change')
    else
      @didGroup.prop('disabled', true)
      @didGroup.val('').trigger('change')

# показать номера
# при изменении красивости или кол-ва колонок или сортировки
  showNumbersList: =>
    ndcTypeId = @ndcType.val()
    cityId = @city.val()
    didGroupVal = @didGroup.val()

    if cityId and ndcTypeId == 'number' # выбирать пока только для номера. Потом еще для 7800
      @numbersListFilter.slideDown()
    else
      @numbersListFilter.slideUp()

    if cityId
      @numbersList.html('')
      $.get '/uu/voip/get-free-numbers', {
        cityId: cityId,
        didGroupId: didGroupVal,
        rowClass: @numbersListClass.val(),
        orderByField: @numbersListOrderByField.val(),
        orderByType: @numbersListOrderByType.val()
        mask: @numbersListMask.val()
        limit: @numbersListLimit.val()
        ndcTypeId: ndcTypeId
      }, (html) =>
        @numbersList.html(html) # обновить значения
        if @numbersList.find('input').length > 1 # есть чекбоксы - показать 'выбрать все'
          @numbersListSelectAll.show()
        else
          @numbersListSelectAll.hide()
        @showTariffDiv()

    else
      @numbersList.html('')
      @numbersListSelectAll.hide()
      @showTariffDiv()

# выбрать все / снять выделение
  selectAllNumbers: =>
    isChecked = @numbersListSelectAllCheckbox.is(':checked')
    @numbersList.find('input').prop('checked', isChecked)
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
    cityId = @city.val()
    $.get '/uu/voip/get-tariff-periods', {serviceTypeId: @voipServiceTypeIdVal, currency: @currencyVal, cityId: cityId, isWithEmpty: 1, format: 'options', isPostpaid: @isPostpaid}, (html) =>
      @tariffPeriod.val('').html(html) # обновить значения
      @tariffPeriod.trigger('change')

# при сабмите формы
  onFormSubmit: (e) =>
# чтобы раньше времени не сабмитить, когда юзер нажимает enter в фильтре
    if not @tariffPeriod.val()
      e.stopPropagation()
      e.preventDefault()

new AccountTariffEdit()