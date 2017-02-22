$ -> window.KitrixCorePlugins = new KitrixCorePlugins()

class KitrixCorePlugins

  @params = null

  constructor: ->

    if KitrixCorePluginsParams?
      @params = KitrixCorePluginsParams
    return

  ###
    Enable plugin by ID
    @param id - PID of plugin (kitrix/core)
  ###
  enable: (id) ->

    plugin = @params.plugins[id]

    @sRequest 'enable', {
      pid: id
    }, (isSuccess, response) ->

      if (isSuccess)
        alertify.success "Плагин #{plugin.title} включен!"

      # update table
      return

    return

  ###
    Disable plugin by ID
    @param id - PID of plugin (kitrix/core)
  ###
  disable: (id) ->

    plugin = @params.plugins[id]

    alertify.confirm "Вы действительно хотите выключить плагин #{plugin.title}?
      Вы сможете включить его позже, все данные/файлы созданные плагином останутся.
      Если вы использовали API плагина, оно станет недоступно."
    , =>

      @sRequest 'disable', {
        pid: id
      }, (isSuccess, response) ->

        if (isSuccess)
          alertify.success "Плагин #{plugin.title} отключен!"

        # update table
        return

    return

  ###
    Remove plugin by ID with full data drop (+files unlink, +composer remove)
    @param id - PID of plugin (kitrix/core)
  ###
  remove: (id) ->

    return

  sRequest: (action, data = {}, callback = null) ->

    checkResponseClosure = (success = false, r) ->

      console.log r, r.msg
      if (r? and r.error?)
        alertify.error r.msg

      callback success, r
      return

    $.ajax {
      url: @params.url
      data: $.extend({
        action: action
      }, data)

      success: (r) -> checkResponseClosure yes, JSON.parse(r)
      error: (r) -> checkResponseClosure no, JSON.parse(r.responseText)
    }

    return