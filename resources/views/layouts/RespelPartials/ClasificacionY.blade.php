@extends('layouts.app')
@section('htmlheader_title', 'Clasificación Y')
@section('contentheader_title')
<span style="background-image: linear-gradient(40deg, #FF856D, #CC0000); padding-right:30vw; position:relative; overflow:hidden;">
	Clasificación Y
  <div style="background-color:#ecf0f5; position:absolute; height:145%; width:40vw; transform:rotate(30deg); right:-20vw; top:-45%;"></div>
</span>
@endsection
@section('main-content')
	<div class="container-fluid spark-screen">
		<div class="row">
			<div class="col-md-16 col-md-offset-0">
				<!-- box -->
				<div class="box">
					<!-- box-header -->
					<div class="box-header">
					<h3 class="box-title">Clasificación Y, según Decreto Número 4741</h3>
					</div>
					<!-- /.box-header -->
					<!-- box-body -->
					<div class="box-body">
					<table id="Clasificacion" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Código</th>
								<th>Descripción</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Y1</td>
								<td>Desechos clínicos resultantes de la atención médica prestada en hospitales, centros médicos y clínicas</td>
							</tr>
							<tr>
								<td>Y1.1</td>
								<td>Desechos clinicos anatomopatologicos resultantes de la atención en salud en Hospitales, consultorios, clinicas y otros.</td>
							</tr>
							<tr>
								<td>Y1.2</td>
								<td>Desechos clinicos biosanitarios resultantes de la atención en salud en Hospitales, consultorios, clinicas y otros.</td>
							</tr>
							<tr>
								<td>Y1.3</td>
								<td>Desechos clinicos cortopunzantes resultantes de la atención en salud en Hospitales, consultorios, clinicas y otros.</td>
							</tr>
							<tr>
								<td>Y1.4</td>
								<td>Desechos de animales y afines de animales</td>
							</tr>
							<tr>
								<td>Y2</td>
								<td>Desechos resultantes de la producción y preparación de productos farmacéuticos</td>
							</tr>
							<tr>
								<td>Y3</td>
								<td>Desechos de medicamentos y productos farmacéuticos</td>
							</tr>
							<tr>
								<td>Y4</td>
								<td>Desechos resultantes de la producción, la preparación y la utilización de biocidas y productos fitofarmacéuticos</td>
							</tr>
							<tr>
								<td>Y4.1</td>
								<td>Plaguicidas, biocidas, productos fitofarmacéuticos obsoletos. (Plaguicidas, insecticidas y herbicidas para control de plagas fuera de especificaciones, caducados o en desuso.)</td>
							</tr>
							<tr>
								<td>Y4.2</td>
								<td>Elementos o materiales contaminados con plaguicidas (Accesorios contaminados (mangueras, boquillas, máquinas), trampas para plagas, EPP contaminados con plaguicidas.)</td>
							</tr>
							<tr>
								<td>Y4.3</td>
								<td>Tierra o sedimentos impregnados con plaguicidas, biocidas o productos fitofarmacéuticos, Suelo contaminado por derrame de RF-254n, lodos contaminados con plaguicidas.</td>
							</tr>
							<tr>
								<td>Y4.4</td>
								<td>Residuos de bolsas plásticas impregnadas de plaguicidas o biocidas.</td>
							</tr>
							<tr>
								<td>Y4.5</td>
								<td>Envases, recipientes, canecas, bidones, bolsas plasticas, de papel, lonas o contenedores que contienen o que están contaminados con plaguicidas, biocidas o productos fitofarmacéuticos. (Residuos de empaques y envases de plaguicidas, generados durante actividades de fumigación.)</td>
							</tr>
							<tr>
								<td>Y4.6</td>
								<td>Otros residuos de plaguicidas, biocidas o productos farmaceuticos no calsificados previamente. (Residuos de plaguicidas o biocidas no que puedan clasificarse en las subdivisiones Y4.1, Y4.2, Y4.3, Y4.4, o Y4.5 (o A4030.1, A4030.2, A4030.3, o A4030.5) previa verificación exhaustiva.)</td>
							</tr>
							<tr>
								<td>Y5</td>
								<td>Desechos resultantes de la fabricación, preparación y utilización de productos químicos para la preservación de la madera</td>
							</tr>
							<tr>
								<td>Y6</td>
								<td>Desechos resultantes de la producción, la preparación y la utilización de disolventes orgánicos</td>
							</tr>
							<tr>
								<td>Y7</td>
								<td>Desechos, que contengan cianuros, resultantes del tratamiento térmico y las operaciones de temple</td>
							</tr>
							<tr>
								<td>Y8</td>
								<td>Desechos de aceites minerales no aptos para el uso a que estaban destinados</td>
							</tr>
							<tr>
								<td>Y8.1</td>
								<td>Aceite lubricante usado (aceite lubricante mineral, sintéctico, hidraúlico usado)</td>
							</tr>
							<tr>
								<td>Y8.2</td>
								<td>Elementos o materiales contaminados con aceite lubricante usado: estopas, textiles, plasticos, caucho, sierra, geomenbranas, filtros, aserrin, grasas minerales, tapas, EPP, madera entre otros.</td>
							</tr>
							<tr>
								<td>Y8.3</td>
								<td>Lodos, tierra o sedimentos impregnados de aceite lubricante usado</td>
							</tr>
							<tr>
								<td>Y8.4</td>
								<td>Mezclas de aceite lubricante usado con agua</td>
							</tr>
							<tr>
								<td>Y8.5</td>
								<td>Aceites dielectricos de desecho con una concentración menor a 50mg/kg, 50ppm de PCB. Si el aceite dielectrico contiene 50ppm o más de PCB, clasifiquelo por la correintes Y10.2  o A3180.2</td>
							</tr>
							<tr>
								<td>Y8.6</td>
								<td>Envases, recipientes, canecas, bidones, o contenedores que contienen o que estan contaminados con aceites usados</td>
							</tr>
							<tr>
								<td>Y8.7</td>
								<td>Otros desechos de mezclas de aceite y agua no clasificados previamente. </td>
							</tr>
							<tr>
								<td>Y9</td>
								<td>Mezclas y emulsiones de desechos de aceite y agua o de hidrocarburos y agua</td>
							</tr>
							<tr>
								<td>Y9.1</td>
								<td>Lodos y cortes de perforación base aceite, borras y lodos aceitosos.</td>
							</tr>
							<tr>
								<td>Y9.2</td>
								<td>Elementos o materiales contaminados con hidrocarburos, estopas, textiles, plasticos, caucho, sierra, geomenbranas, madera entre otros.</td>
							</tr>
							<tr>
								<td>Y9.3</td>
								<td>Sólidos o semisólidos impregnados con hidrocarburos, tierra, suelo, arena, aserrin.</td>
							</tr>
							<tr>
								<td>Y9.4</td>
								<td>Mezclas o emulsiones líquidas de agua con hidrocarburo con contenidos de sólidos <15% e hidrocarburo >3%</td>
							</tr>
							<tr>
								<td>Y9.5</td>
								<td>Envases recipientes canecas, bidones o contenedores que contienen o que estan contaminados con hidrocarburos. </td>
							</tr>
							<tr>
								<td>Y9.6</td>
								<td>Otros desechos de mezclas y emulsiones de hidrocarburos y agua no clasificados previamente</td>
							</tr>
							<tr>
								<td>Y10</td>
								<td>Sustancias y artículos de desecho que contengan, o estén contaminados por, bifenilos policlorados (PCB), terfenilos policlorados (PCT) o bifenilos polibromados (PBB)</td>
							</tr>
							<tr>
								<td>Y11</td>
								<td>Residuos alquitranados resultantes de la refinación, destilación o cualquier otro tratamiento pirolítico</td>
							</tr>
							<tr>
								<td>Y12</td>
								<td>Desechos resultantes de la producción, preparación y utilización de tintas, colorantes, pigmentos, pinturas, lacas o barnices</td>
							</tr>
							<tr>
								<td>Y13</td>
								<td>Desechos resultantes de la producción, preparación y utilización de resinas, látex, plastificantes o colas y adhesivos</td>
							</tr>
							<tr>
								<td>Y14</td>
								<td>Sustancias químicas de desecho, no identificadas o nuevas, resultantes de la investigación y el desarrollo o de las actividades de enseñanza y cuyos efectos en el ser humano o el medio ambiente no se conozcan</td>
							</tr>
							<tr>
								<td>Y15</td>
								<td>Desechos de carácter explosivo que no estén sometidos a una legislación diferente</td>
							</tr>
							<tr>
								<td>Y16</td>
								<td>Desechos resultantes de la producción; preparación y utilización de productos químicos y materiales para fines fotográficos</td>
							</tr>
							<tr>
								<td>Y17</td>
								<td>Desechos resultantes del tratamiento de superficie de metales y plásticos</td>
							</tr>
							<tr>
								<td>Y18</td>
								<td>Residuos resultantes de las operaciones de eliminación de desechos industriales</td>
							</tr>
							<tr>
								<td>Y19</td>
								<td>Desechos que tengan como constituyentes: Metales carbonilos</td>
							</tr>
							<tr>
								<td>Y20</td>
								<td>Desechos que tengan como constituyentes: Berilio, compuestos de berilio</td>
							</tr>
							<tr>
								<td>Y21</td>
								<td>Desechos que tengan como constituyentes: Compuestos de cromo hexavalente</td>
							</tr>
							<tr>
								<td>Y22</td>
								<td>Desechos que tengan como constituyentes: Compuestos de cobre</td>
							</tr>
							<tr>
								<td>Y23</td>
								<td>Desechos que tengan como constituyentes: Compuestos de zinc</td>
							</tr>
							<tr>
								<td>Y24</td>
								<td>Desechos que tengan como constituyentes: Arsénico, compuestos de arsénico</td>
							</tr>
							<tr>
								<td>Y25</td>
								<td>Desechos que tengan como constituyentes: Selenio, compuestos de selenio</td>
							</tr>
							<tr>
								<td>Y26</td>
								<td>Desechos que tengan como constituyentes: Cadmio, compuestos de cadmio</td>
							</tr>
							<tr>
								<td>Y27</td>
								<td>Desechos que tengan como constituyentes: Antimonio, compuestos de antimonio</td>
							</tr>
							<tr>
								<td>Y28</td>
								<td>Desechos que tengan como constituyentes: Telurio, compuestos de telurio</td>
							</tr>
							<tr>
								<td>Y29</td>
								<td>Desechos que tengan como constituyentes: Mercurio, compuestos de mercurio</td>
							</tr>
							<tr>
								<td>Y30</td>
								<td>Desechos que tengan como constituyentes: Talio, compuestos de talío</td>
							</tr>
							<tr>
								<td>Y31</td>
								<td>Desechos que tengan como constituyentes: Plomo, compuestos de plomo</td>
							</tr>
							<tr>
								<td>Y32</td>
								<td>Desechos que tengan como constituyentes: Compuestos inorgánicos de flúor, con exclusión del fluoruro calcico</td>
							</tr>
							<tr>
								<td>Y33</td>
								<td>Desechos que tengan como constituyentes: Cianuros inorgánicos</td>
							</tr>
							<tr>
								<td>Y34</td>
								<td>Desechos que tengan como constituyentes: Soluciones ácidas o ácidos en forma sólida</td>
							</tr>
							<tr>
								<td>Y35</td>
								<td>Desechos que tengan como constituyentes: Soluciones básicas o bases en forma sólida</td>
							</tr>
							<tr>
								<td>Y36</td>
								<td>Desechos que tengan como constituyentes: Asbesto (polvo y fibras)</td>
							</tr>
							<tr>
								<td>Y37</td>
								<td>Desechos que tengan como constituyentes: Compuestos orgánicos de fósforo</td>
							</tr>
							<tr>
								<td>Y38</td>
								<td>Desechos que tengan como constituyentes: Cianuros orgánicos</td>
							</tr>
							<tr>
								<td>Y39</td>
								<td>Desechos que tengan como constituyentes: Fenoles, compuestos fenólicos, con inclusión de clorofenoles</td>
							</tr>
							<tr>
								<td>Y40</td>
								<td>Desechos que tengan como constituyentes: Éteres</td>
							</tr>
							<tr>
								<td>Y41</td>
								<td>Desechos que tengan como constituyentes: Solventes orgánicos halogenados</td>
							</tr>
							<tr>
								<td>Y42</td>
								<td>Desechos que tengan como constituyentes: Disolventes orgánicos, con exclusión de disolventes halogenados</td>
							</tr>
							<tr>
								<td>Y43</td>
								<td>Desechos que tengan como constituyentes: Cualquier sustancia del grupo de los dibenzofuranos policlorados</td>
							</tr>
							<tr>
								<td>Y44</td>
								<td>Desechos que tengan como constituyentes: Cualquier sustancia del grupo de las dibenzoparadioxinas policloradas</td>
							</tr>
							<tr>
								<td>Y45</td>
								<td>Desechos que tengan como constituyentes: Compuestos organohalogenados, que no sean las sustancias mencionadas en los campos anteriores (por ejemplo, Y39, Y41, Y42, Y43, Y44).</td>
							</tr>
						</tbody>
					</table>
					</div>
					<!-- /.box-body -->
				</div>
				<!-- /.box -->
			</div>
		</div>
	</div>
@endsection