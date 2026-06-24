<div class="box-tools col-md-12 collapse in Respel`+id_div+`">
	<button type="button" class="btn btn-box-tool boton pull-right" style="color: red; font-size: 1.3em;" onclick="RemoveRespel(`+id_div+`,`+contadorRespel[id_div]+`)" title="Eliminar"><i class="fa fa-times"></i></button>
</div>
<div id="Repel`+id_div+contadorRespel[id_div]+`" class="col-md-12 box box-warning collapse in Respel`+id_div+`">
	<div class="form-group col-md-16">
		<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserrespel') }}</b>" data-content="{{ __('adminlte::message.solserrespeldescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserrespel') }}</label>
		<a class="loadrespelnew`+id_div+contadorRespel[id_div]+`"></a>
		<button type="button" class="btn btn-box-tool boton" style="color: #f39c12;" data-toggle="collapse" data-target=".ContentRespel`+id_div+contadorRespel[id_div]+`" onclick="AnimationMenusForm('.ContentRespel`+id_div+contadorRespel[id_div]+`')" title="Reducir/Ampliar"> <i class="fa fa-minus"></i></button>
		<small class="help-block with-errors">*</small>
		<select name="FK_SolResRg[`+id_div+`][]" id="FK_SolResRg`+id_div+contadorRespel[id_div]+`" class="form-control" required="">
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
			<input type="number" step=".01" min="0" class="form-control numberKg" id="SolResKgEnviado`+id_div+contadorRespel[id_div]+`" name="SolResKgEnviado[`+id_div+`][]" required="">
		</div>
		<div class="form-group col-md-6 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`">
			<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserembaja') }}</b>" data-content="{{ __('adminlte::message.solserembajadescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserembaja') }}</label>
			<small class="help-block with-errors">*</small>
			<select name="SolResEmbalaje[`+id_div+`][]" id="SolResEmbalaje`+id_div+contadorRespel[id_div]+`" class="form-control" required="">
				<option value="">{{ __('adminlte::message.select') }}</option>
				<option value="99">{{ __('adminlte::message.solserembaja1') }}</option>
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
		<div class="form-group col-md-6 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`">			
			<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Cantidad de embalaje</b>" data-content="Cantidad de unidades de embalaje declaradas (canecas, bolsas, cajas, etc.). <b>Nota:</b> Para bolsas puede ser un aproximado."><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>Cantidad de embalaje</label>
			<small class="help-block with-errors">*</small>
			<input type="number" min="0" class="form-control" id="SolResCantEmbalaje`+id_div+contadorRespel[id_div]+`" name="SolResCantEmbalaje[`+id_div+`][]" required>
		</div>
		<div class="form-group col-md-16 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`" style="text-align: center;">
			<div class="form-group col-md-12">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.solserdimension') }}</b>" data-content="{{ __('adminlte::message.solserdimensiondescrit') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.solserdimension') }}</label>
			</div>
			<div class="form-group col-md-4">
				<label for="SolResAlto`+id_div+contadorRespel[id_div]+`">{{ __('adminlte::message.solserdimension1') }}</label>
				<input type="number" step=".01" max="30" min="0" class="form-control numberDimension" id="SolResAlto`+id_div+contadorRespel[id_div]+`" name="SolResAlto[`+id_div+`][]">
			</div>
			<div class="form-group col-md-4">
				<label for="SolResAncho`+id_div+contadorRespel[id_div]+`">{{ __('adminlte::message.solserdimension2') }}</label>
				<input type="number" step=".01" max="30" min="0" class="form-control numberDimension" id="SolResAncho`+id_div+contadorRespel[id_div]+`" name="SolResAncho[`+id_div+`][]">
			</div>
			<div class="form-group col-md-4">
				<label for="SolResProfundo`+id_div+contadorRespel[id_div]+`">{{ __('adminlte::message.solserdimension3') }}</label>
				<input type="number" step=".01" max="30" min="0" class="form-control numberDimension" id="SolResProfundo`+id_div+contadorRespel[id_div]+`" name="SolResProfundo[`+id_div+`][]">
			</div>
		</div>
		<div class="form-group col-md-12 collapse in ContentRespel`+id_div+contadorRespel[id_div]+`" style="text-align: center;">
			<div class="form-group col-md-12">
				<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requirements') }}</b>" data-content="{{ __('adminlte::message.requirementsdescript') }}"><i style="font-size: 1.8rem; color: Dodgerblue;" class="fas fa-info-circle fa-2x fa-spin"></i>{{ __('adminlte::message.requirements') }}</label>
				<a class="loadrequired`+id_div+contadorRespel[id_div]+`"></a>
			</div>
			<div class="form-group col-md-6" style="border: 2px dashed #00c0ef">
				<div class="form-group col-md-6">
					<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requiredescarguephoto') }}</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiredescarguephotodescrit') }}</p>">
						<label for="SolResFotoDescargue_Pesaje`+id_div+contadorRespel[id_div]+`">{{ __('adminlte::message.requiredescarguephoto') }}</label>
						<a class="loadrequired`+id_div+contadorRespel[id_div]+`"></a>
						<div style="width: 100%; height: 34px;">
							<input type="checkbox" class="fotoswitch" id="SolResFotoDescargue_Pesaje`+id_div+contadorRespel[id_div]+`" data-name="SolResFotoDescargue_PesajeX`+id_div+contadorRespel[id_div]+`"/>
							<input type="text" id="SolResFotoDescargue_PesajeX`+id_div+contadorRespel[id_div]+`" name="SolResFotoDescargue_Pesaje[`+id_div+`][]" hidden value="0">
						</div>
					</label>
				</div>
				<div class="form-group col-md-6">
					<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requiretratamientophoto') }}</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiretratamientophotodescrit') }}</p>">
						<label for="SolResFotoTratamiento`+id_div+contadorRespel[id_div]+`">{{ __('adminlte::message.requiretratamientophoto') }}</label>
						<div style="width: 100%; height: 34px;">
							<input type="checkbox" class="fotoswitch" id="SolResFotoTratamiento`+id_div+contadorRespel[id_div]+`" value="0" data-name="SolResFotoTratamientoX`+id_div+contadorRespel[id_div]+`"/>
							<input type="text" id="SolResFotoTratamientoX`+id_div+contadorRespel[id_div]+`" name="SolResFotoTratamiento[`+id_div+`][]" hidden value="0">
						</div>
					</label>
				</div>
			</div>
			<div class="form-group col-md-6" style="border: 2px dashed #00c0ef">
				<div class="form-group col-md-6">
					<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requiredescarguevideo') }}</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiredescarguevideodescrit') }}</p>">
						<label for="SolResVideoDescargue_Pesaje`+id_div+contadorRespel[id_div]+`">{{ __('adminlte::message.requiredescarguevideo') }}</label>
						<div style="width: 100%; height: 34px;">
							<input type="checkbox" class="videoswitch" id="SolResVideoDescargue_Pesaje`+id_div+contadorRespel[id_div]+`" data-name="SolResVideoDescargue_PesajeX`+id_div+contadorRespel[id_div]+`"/>
							<input type="text" id="SolResVideoDescargue_PesajeX`+id_div+contadorRespel[id_div]+`" name="SolResVideoDescargue_Pesaje[`+id_div+`][]" hidden value="0">
						</div>
					</label>
				</div>
				<div class="form-group col-md-6">
					<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>{{ __('adminlte::message.requiretratamientovideo') }}</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiretratamientovideodescrit') }}</p>">
						<label for="SolResVideoTratamiento`+id_div+contadorRespel[id_div]+`">{{ __('adminlte::message.requiretratamientovideo') }}</label>
						<div style="width: 100%; height: 34px;">
							<input type="checkbox" class="videoswitch" id="SolResVideoTratamiento`+id_div+contadorRespel[id_div]+`" data-name="SolResVideoTratamientoX`+id_div+contadorRespel[id_div]+`"/>
							<input type="text" id="SolResVideoTratamientoX`+id_div+contadorRespel[id_div]+`" name="SolResVideoTratamiento[`+id_div+`][]" hidden value="0">
						</div>
					</label>
				</div>
			</div>
			<div class="form-group col-md-6" style="border: 2px dashed #00c0ef">
				<div class="form-group col-md-6">
					<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Devoluci??n de Elementos</b>" data-content="<p style='width: 50%'> {{ __('adminlte::message.requiredescarguevideodescrit') }}</p>">
						<label for="SolResDevolucion`+id_div+contadorRespel[id_div]+`">Devoluci??n Embalaje</label>
						<div style="width: 100%; height: 34px;">
							<input type="checkbox" class="videoswitch" id="SolResDevolucion`+id_div+contadorRespel[id_div]+`" data-name="SolResDevolucionX`+id_div+contadorRespel[id_div]+`"/>
							<input type="text" id="SolResDevolucionX`+id_div+contadorRespel[id_div]+`" name="SolResDevolucion[`+id_div+`][]" hidden value="0">
						</div>
					</label>
				</div>
				<div class="form-group col-md-6">
					<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Tratamiento Auditable</b>" data-content="<p style='width: 50%'> Se requiere que el tratamiento del residuo sea auditado por personal del Cliente/Generador ">
						<label for="SolResAuditoria`+id_div+contadorRespel[id_div]+`">Requiere Auditoria</label> 
						<div style="width: 100%; height: 34px;">
							<input type="checkbox" class="auditoriaswitch" id="SolResAuditoria`+id_div+contadorRespel[id_div]+`" data-name="SolResAuditoriaX`+id_div+contadorRespel[id_div]+`"/>
							<input type="text" id="SolResAuditoriaX`+id_div+contadorRespel[id_div]+`" name="SolResAuditoria[`+id_div+`][]" hidden value="0">
						</div>
					</label>
				</div>
				{{-- <div class="form-group col-md-6">
					<label data-placement="auto" data-trigger="hover" data-html="true" data-toggle="popover" title="<b>Cantidad a devolver</b>" data-content="<p style='width: 50%'> Cantidad de embalajes enviados y que deben ser devueltos al respectivo cliente o viceversa</p>">
						<label for="SolResDevolCantidad`+id_div+contadorRespel[id_div]+`">Cantidad a devolver</label>
						<div style="width: 100%; height: 34px;">
							<input type="text" class="form-control" id="SolResDevolCantidad`+id_div+contadorRespel[id_div]+`" data-name="SolResDevolCantidadX`+id_div+contadorRespel[id_div]+`"/>
							<input type="text" id="SolResDevolCantidadX`+id_div+contadorRespel[id_div]+`" name="SolResDevolCantidad[`+id_div+`][]" hidden value="0">
						</div>
					</label>
				</div> --}}
			</div>
		</div>
		<br>
	</div>
</div>