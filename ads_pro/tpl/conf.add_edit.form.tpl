<script language="javascript" type="text/javascript">
	function AddBlok() {
	        var tbl = document.getElementById('blokup');
	        var lastRow = tbl.rows.length;
	        var iteration = lastRow + 1;
	        var row = tbl.insertRow(lastRow);
	        var cellRight = row.insertCell(0);
	        cellRight.innerHTML = iteration + ': ';
	        cellRight = row.insertCell(1);
	        cellRight.setAttribute('align', 'left');
	
	        var el = '<select class="form-select form-select-sm d-inline-block w-auto me-2" name="location[' + iteration + '][mode]" onchange="AddSubBlok(this, ' + iteration + ');"><option value=0>{l_ads_pro:around}</option><option value=1>{l_ads_pro:main}</option><option value=2>{l_ads_pro:not_main}</option><option value=3>{l_ads_pro:category}</option><option value=4>{l_ads_pro:static}</option>[support_news]<option value=5>{l_ads_pro:news}</option>[/support_news]<option value=6>{l_ads_pro:plugins}</option></select>';
	
	        cellRight.innerHTML += el;
	
	        el = '<select class="form-select form-select-sm d-inline-block w-auto" name="location[' + iteration + '][view]"><option value=0>{l_ads_pro:view}</option><option value=1>{l_ads_pro:not_view}</option></select>';
	
	        cellRight.innerHTML += el;
	    }
	    function AddSubBlok(el, iteration) {
	        var subel = null;
	        var subsubel = null;
	        switch (el.value) {
	            case '3':
	                subel = createNamedElement('select', 'location[' + iteration + '][id]');
	                subel.className = 'form-select form-select-sm d-inline-block w-auto ms-2';
	            {
	                category_list
	            }
	                break;
	            case '4':
	                subel = createNamedElement('select', 'location[' + iteration + '][id]');
	                subel.className = 'form-select form-select-sm d-inline-block w-auto ms-2';
	            {
	                static_list
	            }
	                break;
	                [support_news]
	            case '5':
	                subel = createNamedElement('select', 'location[' + iteration + '][id]');
	                subel.className = 'form-select form-select-sm d-inline-block w-auto ms-2';
	            {
	                news_list
	            }
	                break;
	                [/support_news]
	            case '6':
	                subel = createNamedElement('select', 'location[' + iteration + '][id]');
	                subel.className = 'form-select form-select-sm d-inline-block w-auto ms-2';
	            {
	                plugins_list
	            }
	                break;
	        }
	        if (el.nextSibling.name == 'location[' + iteration + '][id]')
	            el.parentNode.removeChild(el.nextSibling);
	        if (subel)
	            el.parentNode.insertBefore(subel, el.nextSibling);
	    }
	    function RemoveBlok() {
	        var tbl = document.getElementById('blokup');
	        var lastRow = tbl.rows.length;
	        if (lastRow > 0) {
	            tbl.deleteRow(lastRow - 1);
	        }
	    }
	    function createNamedElement(type, name) {
	        var element = null;
	        try {
	            element = document.createElement('<' + type + ' name="' + name + '">');
	        } catch (e) {
	        }
	        if (!element || element.nodeName != type.toUpperCase()) {
	            element = document.createElement(type);
	            element.setAttribute("name", name);
	        }
	        return element;
	    }
	</script>
	
	<form method="post" action="?mod=extra-config&amp;plugin=ads_pro&amp;action=[add]add_submit[/add][edit]edit_submit[/edit]">
	    <input type="hidden" name="id" value="[add]0[/add][edit]{id}[/edit]"/>
	    <div class="card mb-4">
	        <div class="card-body">
	            <div class="mb-3 row">
	                <label class="col-sm-3 col-form-label">{l_ads_pro:name}<br/><small class="text-muted">{l_ads_pro:name_d}</small></label>
	                <div class="col-sm-9">
	                    <input type="text" class="form-control" name="name" [edit] value="{name}" [/edit] />
	                </div>
	            </div>
	            
	            <div class="mb-3 row">
	                <label class="col-sm-3 col-form-label">{l_ads_pro:description}<br/><small class="text-muted">{l_ads_pro:description_d}</small></label>
	                <div class="col-sm-9">
	                    <input type="text" class="form-control" name="description" [edit] value="{description}" [/edit] />
	                </div>
	            </div>
	            
	            <div class="mb-3 row">
	                <label class="col-sm-3 col-form-label">{l_ads_pro:type}<br/><small class="text-muted">{l_ads_pro:type_d}</small></label>
	                <div class="col-sm-9">
	                    {type_list}
	                </div>
	            </div>
	            
	            <div class="mb-3 row">
	                <label class="col-sm-3 col-form-label">{l_ads_pro:location}<br/><small class="text-muted">{l_ads_pro:location_d}</small></label>
	                <div class="col-sm-9">
	                    <button type="button" class="btn btn-outline-danger btn-sm me-2" onClick="RemoveBlok();return false;">{l_ads_pro:location_dell}</button>
	                    <button type="button" class="btn btn-outline-primary btn-sm" onClick="AddBlok();return false;">{l_ads_pro:location_add}</button>
	                    <div class="mt-2">
	                        <table id="blokup" class="table table-sm table-borderless">[edit]{location_list}[/edit]</table>
	                    </div>
	                </div>
	            </div>
	            
	            <div class="mb-3 row">
	                <label class="col-sm-3 col-form-label">{l_ads_pro:state}<br/><small class="text-muted">{l_ads_pro:state_d}</small></label>
	                <div class="col-sm-9">
	                    {state_list}
	                </div>
	            </div>
	        </div>
	    </div>
	    
	    <div class="card mb-4">
	        <div class="card-header">
	            <b>{l_ads_pro:sched_legend}</b>
	        </div>
	        <div class="card-body">
	            <div class="mb-3 row">
	                <label class="col-sm-3 col-form-label">{l_ads_pro:start_view}<br/><small class="text-muted">{l_ads_pro:start_view_d}</small></label>
	                <div class="col-sm-9">
	                    <input type="text" class="form-control" name="start_view" [edit] value="{start_view}" [/edit] />
	                </div>
	            </div>
	            
	            <div class="mb-3 row">
	                <label class="col-sm-3 col-form-label">{l_ads_pro:end_view}<br/><small class="text-muted">{l_ads_pro:end_view_d}</small></label>
	                <div class="col-sm-9">
	                    <input type="text" class="form-control" name="end_view" [edit] value="{end_view}" [/edit] />
	                </div>
	            </div>
	        </div>
	    </div>
	    
	    <div class="card mb-4">
	        <div class="card-header">
	            <b>{l_ads_pro:ads_blok_legend}</b>
	        </div>
	        <div class="card-body">
	            <p class="text-muted mb-3">{l_ads_pro:ads_blok_info}</p>
	            <textarea class="form-control font-monospace" name="ads_blok" rows="30">[edit]{ads_blok}[/edit]</textarea>
	        </div>
	    </div>
	
	    <div class="text-center mb-4">
	        <button type="submit" class="btn btn-success">[add]{l_ads_pro:add_submit}[/add][edit]{l_ads_pro:edit_submit}[/edit]</button>
	    </div>
	</form>
