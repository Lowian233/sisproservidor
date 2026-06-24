<div id="Repel`+id_div+contadorRespel[id_div]+`" class="col-md-12 box collapse in Respel`+id_div+`">
	<div class="form-group col-md-16">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserrespel') }}</b>" data-content="{{ __('adminlte::message.solserrespeldescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserrespel') }}</label>
		<a class="loadrespelone`+id_div+contadorRespel[id_div]+`"></a>
		<button type="button" class="btn btn-box-tool boton" style="color: #f39c12;" data-toggle="collapse" data-target=".ContentRespel`+id_div+contadorRespel[id_div]+`" onclick="AnimationMenusForm('.ContentRespel`+id_div+contadorRespel[id_div]+`')" title="Reducir/Ampliar"> <i class="fa fa-minus"></i></button>
		<small class="help-block with-errors">*</small>
		<select name="FK_SolResRg[`+id_div+`][]" id="FK_SolResRg`+id_div+contadorRespel[id_div]+`" class="form-control" required>
		</select>
	</div>
	<div id="RespelData`+id_div+contadorRespel[id_div]+`">
		<div id="RespelCantidadTipo`+id_div+contadorRespel[id_div]+`">
			<div class="form-group col-md-6 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solsertypeunidad') }}</b>" data-content="{{ __('adminlte::message.solsertypeunidaddescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solsertypeunidad') }}</label>
				<select required name="SolResTypeUnidad[`+id_div+`][]" id="SolResTypeUnidad`+id_div+contadorRespel[id_div]+`" class="form-control">
					<option value="">{{ __('adminlte::message.select') }}</option>
					<option value="99">{{ __('adminlte::message.solserunidad1') }}</option>
					<option value="98">{{ __('adminlte::message.solserunidad2') }}</option>
				</select>
			</div>
			<div class="form-group col-md-6 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solsercantidad') }}</b>" data-content="{{ __('adminlte::message.solsercantidaddescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solsercantidad') }}</label>
				<input type="number" step=".1" min="0" class="form-control numberKg" id="SolResCantiUnidad`+id_div+contadorRespel[id_div]+`" name="SolResCantiUnidad[`+id_div+`][]">
			</div>
		</div>

		<div class="form-group col-md-6 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`">
			<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solsercantidadkg') }}</b>" data-content="{{ __('adminlte::message.solsercantidadkgdescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solsercantidadkg') }}</label>
			<small class="help-block with-errors">*</small>
			<input type="number" step=".01" min="0" class="form-control numberKg" id="SolResKgEnviado`+id_div+contadorRespel[id_div]+`" name="SolResKgEnviado[`+id_div+`][]" required>
		</div>
		<div class="form-group col-md-6 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`">
			<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserembaja') }}</b>" data-content="{{ __('adminlte::message.solserembajadescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserembaja') }}</label>
			<small class="help-block with-errors">*</small>
			<select name="SolResEmbalaje[`+id_div+`][]" id="SolResEmbalaje`+id_div+contadorRespel[id_div]+`" class="form-control selectdeembalaje" required>
				<option value="">{{ __('adminlte::message.select') }}</option>
				<option value="99" data-image="https://picsum.photos/536/354">{{ __('adminlte::message.solserembaja1') }}</option>
				<option value="98">{{ __('adminlte::message.solserembaja2') }}</option>
				<option value="97">{{ __('adminlte::message.solserembaja3') }}</option>
				<option value="96">{{ __('adminlte::message.solserembaja4') }}</option>
				<option value="95">{{ __('adminlte::message.solserembaja5') }}</option>
				<option value="94">{{ __('adminlte::message.solserembaja6') }}</option>
				<option value="93">{{ __('adminlte::message.solserembaja7') }}</option>
				<option value="92">{{ __('adminlte::message.solserembaja8') }}</option>
				<option value="91">{{ __('adminlte::message.solserembaja9') }}</option>
				<option value="90">{{ __('adminlte::message.solserembaja10') }}</option>
				<option value="89">{{ __('adminlte::message.solserembaja11') }}</option>
				<option value="88">{{ __('adminlte::message.solserembaja12') }}</option>
				<option value="87">{{ __('adminlte::message.solserembaja13') }}</option>
				<option value="86">{{ __('adminlte::message.solserembaja14') }}</option>
			</select>
		</div>
		<br>
	</div>
</div>
<div id="AddRespel`+id_div+`" class="form-group col-md-16 form-group col-md-offset-5 col-xs-offset-5 collapse in Respel`+id_div+`">
	<a onclick="AgregarResPel(`+id_div+`,'`+ID_Gener+`')" id="Agregar`+id_div+contadorRespel[id_div]+`" class="btn btn-success" data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solseraddrespel') }}</b>" data-content="{{ __('adminlte::message.solseraddrespeldescrit') }}"><i class="fas fa-plus"></i> {{ __('adminlte::message.solseraddrespel') }}</a><br><br>
</div>
