class AccounttariffEdit

  country = null
  numberType = null
  regions = null
  didGroup = null
  numbersList = null
  numbersListSelectAll = null
  numbersListClass = null
  numbersListOrderByField = null
  numbersListOrderByType = null
  numbersListMask = null

  # инициализация
  constructor: () ->
    setTimeout () =>
      @country = $('#voipCountryId').on('change', @onCountryChange)
      @numberType = $('#voipNumberType') #.on('change', @onNumberTypeOrRegionsChange)
      @regions = $('#voipRegions').on('change', @onNumberTypeOrRegionsChange)
      @didGroup = $('#voipDidGroup').on('change', @showNumbersList)
      @numbersList = $('#voipNumbersList')
      @numbersListSelectAll = $('#voipNumbersListSelectAll input').on('change', @selectAllNumbers)
      @numbersListClass = $('#voipNumbersListClass').on('change', @showNumbersList)
      @numbersListOrderByField = $('#voipNumbersListOrderByField').on('change', @showNumbersList)
      @numbersListOrderByType = $('#voipNumbersListOrderByType').on('change', @showNumbersList)
      @numbersListMask = $('#voipNumbersListMask').on('change', @showNumbersList)
    , 200 # Потому что select2 рендерит чуть позже. @todo

# при изменении страны
  onCountryChange: () =>
    countryVal = @country.val()
    if countryVal

      $.get '/uu/voip/get-number-types', {countryId: countryVal, isWithEmpty: true, format: 'options'}, (html) =>
        @numberType.html(html) # обновить значения
        @numberType.prop("disabled", false)
        @numberType.val('').trigger('change')

      $.get '/uu/voip/get-cities', {countryId: countryVal, isWithEmpty: true, format: 'options'}, (html) =>
        @regions.html(html) # обновить значения
        @regions.prop("disabled", false)
        @regions.val('').trigger('change')

    else
      @numberType.prop("disabled", true)
      @numberType.val('').trigger('change')

      @regions.prop("disabled", true)
      @regions.val('').trigger('change')

# при изменении типа или региона
  onNumberTypeOrRegionsChange: =>
#    numberTypeVal = @numberType.val()
    regionsVal = @regions.val()
    if regionsVal
      $.get '/uu/voip/get-did-groups', {cityId: regionsVal, isWithEmpty: true, format: 'options'}, (html) =>
        @didGroup.html(html) # обновить значения
        @didGroup.prop("disabled", false)
        @didGroup.val('').trigger('change')
    else
      @didGroup.prop("disabled", true)
      @didGroup.val('').trigger('change')
    @showNumbersList()

# показать номера
# при изменении красивости или кол-ва колонок или сортировки
  showNumbersList: =>
    regionsVal = @regions.val()
    didGroupVal = @didGroup.val()
    if regionsVal
      $.get '/uu/voip/get-free-numbers', {
        cityId: regionsVal,
        didGroupId: didGroupVal,
        rowClass: @numbersListClass.val(),
        orderByField: @numbersListOrderByField.val(),
        orderByType: @numbersListOrderByType.val()
        mask: @numbersListMask.val()
      }, (html) =>
        @numbersList.html(html) # обновить значения
    else
      @numbersList.html('')

# выбрать все / снять выделение
  selectAllNumbers: =>
    isChecked = @numbersListSelectAll.is(':checked')
    @numbersList.find('input').prop('checked', isChecked)

new AccounttariffEdit()