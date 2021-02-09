@extends('layout.master-wiki')
@section('content')
<div class="wiki">
		<div class="wrapper">
			<div class="breadcrumb">
				<span><a href="#" class="trn" data-trn-key="wikiquests"></a></span>
				<span>Relics of the Old Empire - Lv. 74</span>
			</div>
			<div class="content-box content-styled">
				<!-- Title -->
				<h2 class="section-title">
				Relics of the Old Empire - Lv. 74
				</h2>
				<div class="content-scrolling">
					<table width="500" class="table-list fluid-table">
						<thead>
							<tr>
								<th colspan="2" class="trn" data-trn-key="quest_details"></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td width="90" class="color-accent" class="trn" data-trn-key="race">Races</td>
								<td class="trn" data-trn-key="all"></td>
							</tr>
							<tr>
								<td class="color-accent" class="trn" data-trn-key="territory_type">Type</td>
								<td class="trn" data-trn-key="mission_completion_repeatable"></td>
							</tr>
							<tr>
								<td class="color-accent" class="trn" data-trn-key="global_level">Level</td>
								<td>74</td>
							</tr>
							<tr>
								<td class="color-accent" class="trn" data-trn-key="global_rewards"></td>
								<td>
									<div>{{ __('quest-template.step_1') }}</div>
								</td>
							</tr>
							<tr>
								<td class="color-accent">NPC</td>
								<td>Ghost of Adventurer</td>
							</tr>
						</tbody>
					</table>
				</div>

				<h3 class="section-subtitle"><span  class="trn" data-trn-key="global_guide"></span></h3>

				<p>1. {{ __('quest-template.step_2') }} <img class="icon-image-small" src="{{ asset('l2improved/img/Icon/Texture/etc_pouch_gray_i00.png') }}" />Broken Relic Part.</p>
				<p>2. {{ __('quest-template.step_3') }}</p> 
				<p>3. {{ __('quest-template.step_4') }} <img class="icon-image-small" src="{{ asset('l2improved/img/Icon/Texture/etc_pouch_gray_i00.png') }}" />Broken Relic Part. {{ __('quest-template.step_5') }}</p> 
				<p>4. {{ __('quest-template.step_6') }}</p> 
			</div>
		</div>
	</div>
@stop