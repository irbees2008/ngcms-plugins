<form method="post" action="">
	<div class="panel-body" style="font-family: Franklin Gothic Medium;text-transform: uppercase;color: #9f9f9f;">Настройки плагина</div>
	<div class="table-responsive">
	<table class="table table-striped">
      <tr>
        <td class="col-xs-6 col-sm-6 col-md-7">
		  <h6 class="media-heading text-semibold">Количество дней:</h6>
		  <span class="text-muted text-size-small hidden-xs">Введите период дней, через сколько суток запрос на удаления профиля от пользователя вступит в силу. (по умолчанию 31)</span>
		</td>
        <td class="col-xs-6 col-sm-6 col-md-5">
			<input name="day_period" type="text" size="4" value="{{ day_period }}" />
        </td>
      </tr>
      <tr>
        <td class="col-xs-6 col-sm-6 col-md-7">
		  <h6 class="media-heading text-semibold">Уведомление администратору:</h6>
		  <span class="text-muted text-size-small hidden-xs">Уведомлять администратора об удалении пользователем своего профиля. (по умолчанию нет)</span>
		</td>
        <td class="col-xs-6 col-sm-6 col-md-5">
			<select name="notif_pm">{{ notif_pm }}</select>
        </td>
      </tr>  
	</table>
	</div>
	<div class="card-footer" align="center">
		<button type="submit" name="submit" class="btn btn-outline-primary">Сохранить</button>
	</div>
</form>