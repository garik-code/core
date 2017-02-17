$ -> window.KitrixCorePlugins = new KitrixCorePlugins()

class KitrixCorePlugins

  @params = null

  constructor: ->

    @params = KitrixCorePluginsParams;
    return

  ###
    Enable plugin by ID
    @param id - PID of plugin (kitrix/core)
  ###
  enable: (id) ->

    return

  ###
    Disable plugin by ID
    @param id - PID of plugin (kitrix/core)
  ###
  disable: (id) ->

    plugin = @params.plugins[id];
    allow = confirm("
      Вы действительно хотите выключить плагин #{plugin.title}?
      Вы сможете включить его позже, все данные/файлы созданные плагином останутся.
      Если вы использовали API плагина, оно станет недоступно.
    ")

    return unless allow

    @sRequest 'disable', {
      pid: id
    }, (isSuccess, response) ->

      if (!isSuccess)

        alert("Не удалось отключить плагин #{plugin.title}!")
        return

      # update table
      console.log "updated!", response

      return

    return

  ###
    Remove plugin by ID with full data drop (+files unlink, +composer remove)
    @param id - PID of plugin (kitrix/core)
  ###
  remove: (id) ->

    return

  sRequest: (action, data = {}, callback = null) ->

    $.ajax {
      url: @params.url
      data: $.extend({
        action: action
      }, data)

      success: (r) -> callback yes, r
      error: (r) -> callback no, r
    }

    return