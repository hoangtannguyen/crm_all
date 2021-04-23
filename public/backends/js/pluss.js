$(document).ready(function(){
	$("#currency2").inputmask();
	$("#currency2").inputmask({removeMaskOnSubmit: true});
	$('.toggle-cs').on('click', function(e) {
		e.preventDefault();
		$(this).closest('.list-item').find('.list-child').slideToggle();
	});

	if($('.check__checkbox_all').length > 0) {
		$('.check__checkbox_all').each(function() {
			var count = $(this).find('.list-child input[type="checkbox"]').length;
			var check = $(this).find('.list-child input[type="checkbox"]:checked').length;
			if(count == check) $(this).find('.parent-check').prop('checked', true);
		});
	}

	$('.check__checkbox_all .parent-check').on('change', function() {
		if($(this).prop('checked')) {
			$(this).closest('.check__checkbox_all').find('.list-child input[type="checkbox"]').prop('checked', true);
		}else{
			$(this).closest('.check__checkbox_all').find('.list-child input[type="checkbox"]').prop('checked', false);
		}
	});

	$('.check__checkbox_all .list-child input[type="checkbox"]').on('change', function() {
		var count = $(this).closest('.check__checkbox_all').find('.list-child input[type="checkbox"]').length;
		var check = $(this).closest('.check__checkbox_all').find('.list-child input[type="checkbox"]:checked').length;
		if(count == check) $(this).closest('.check__checkbox_all').find('.parent-check').prop('checked', true);
			else $(this).closest('.check__checkbox_all').find('.parent-check').prop('checked', false);
	});

	$(".eq-check:checkbox").click(function(){
		var column = "table ." + $(this).attr("name");
		$(column).toggle();
	});

	$(".eqsupplie-check:checkbox").click(function(){
		var column = "table ." + $(this).attr("name");
		$(column).toggle();
	});

	$('.btnprn').printPage();
	
	$('.equiment-table-show').click(function() {
		$(this).siblings(".equiment-check-all").addClass('eq').slideToggle();
	 });

	$('#eq_cates').on('change', function() {
		var id = $(this).val();
		var action = $(this).closest('form').attr('data-filter');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				if(data.check == 'true') {
					$('#equi_cat_device').html(data.html_devices);
					$('#equi_cat_device .select2').select2();
				}
			}
		});
	});

	$('#eq_department').on('change', function() {
		var id = $(this).val();
		var action = $(this).closest('form').attr('data-filter');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				if(data.check == 'true') {
					$('#officer_charge_id_device').html(data.html_officer_charge_device);
					$('#officer_charge_id_device .select2').select2();
					$('#officer_department_charge_id_device').html(data.html_officer_department_charge_device);
					$('#officer_department_charge_id_device .select2').select2();
					$('#equipment_user_use_device').html(data.html_user_use_device);
					$('#equipment_user_use_device .select2').select2();
					$('#equipment_user_training_device').html(data.html_user_training_device);
					$('#equipment_user_training_device .select2').select2();
				}
			}
		});
	});

	$('#modal_hand_department').on('change', function() {
		var id = $(this).val();
		var action = $(this).closest('form').attr('data-filter');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				if(data.check == 'true') {
					$('#modal_hand_user').html(data.html);
					$('#modal_hand_user .select2').select2();
					$('#modal_hand_user_use').html(data.html_user_use);
					$('#modal_hand_user_use .select2').select2();
				}
			}
		});
	});

	$('#ballot_device').on('change', function() {
		var id = $(this).val();
		var action = $(this).closest('form').attr('data-filter');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				if(data.check == 'true') {
					if($(".ballot_device tr[data-id="+id+"]").length > 0){
						var quanlity = parseInt($(".ballot_device tr[data-id="+id+"]").find(".quanlity-z").val());
						var currency = parseInt($(".ballot_device tr[data-id="+id+"]").find(".currency-z").val());
						var abc = parseInt($(".ballot_device tr[data-id="+id+"]").find(".total").text());
						total = abc+currency;
						amount = quanlity + 1;
						$(".ballot_device tr[data-id="+id+"]").find(".quanlity-z").val(amount);
						$(".ballot_device tr[data-id="+id+"]").find(".total").html(total);
					}else{
						$('tbody.ballot_device').append(data.html);
					}
					
				}
			}
		});
	});


	$('#supplieBallot').on('change', function() {
		var id = $(this).val();
		var action = $(this).closest('form').attr('data-filter');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				console.log(data);
				if(data.check == 'true') {
					if($(".ballot_device tr[data-id="+id+"]").length > 0){
						var quanlity = parseInt($(".ballot_device tr[data-id="+id+"]").find(".quanlity-z").val());
						var currency = parseInt($(".ballot_device tr[data-id="+id+"]").find(".currency-z").val());
						var abc = parseInt($(".ballot_device tr[data-id="+id+"]").find(".total").text());
						total = abc+currency;
						amount = quanlity + 1;
						$(".ballot_device tr[data-id="+id+"]").find(".quanlity-z").val(amount);
						$(".ballot_device tr[data-id="+id+"]").find(".total").html(total);
					}else{
						$('tbody.ballot_device').append(data.html);
					}
				}
			}
		});
	});


	$("body").on("click",".remove-ballot",function(){
		$(this).parents('tr').first().remove();
	});
	
	$("body").on("keyup",".quanlity-z",function(){
		var value = $(this).val();
		var currency =  $(this).parents('tr').find(".currency-z").val();
            total = value*currency;
			$(this).parents('tr').find("td.total").text(total);
	});
	$("body").on("keyup",".currency-z",function(){
		var value = $(this).val();
        var quanlity =  $(this).parents('tr').find(".quanlity-z").val();
            total = value*quanlity;
			$(this).parents('tr').find("td.total").text(total);
	});
	$('body').on('click','.btn-ballot', function() {
		var id = $(this).attr('data-id');
		var action = $(this).parents('tbody').attr('action');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				if(data.check == 'true') {
					$('.ballot-title').html(data.html_ballot);
					$('.add-ballot-eq').html(data.html);
					$("#ballot-modal").modal('show');
				}
			}
		});
	});

	$('#eq_supplie_department').on('change', function() {
		var id = $(this).val();
		var action = $(this).closest('form').attr('data-filter');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				if(data.check == 'true') {
					$('#officer_charge_id').html(data.html_officer_charge);
					$('#officer_charge_id .select2').select2();
					$('#officer_department_charge_id').html(data.html_officer_department_charge);
					$('#officer_department_charge_id .select2').select2();
					$('#equipment_user_use').html(data.html_user_use);
					$('#equipment_user_use .select2').select2();
					$('#equipment_user_training').html(data.html_user_training);
					$('#equipment_user_training .select2').select2();
				}
			}
		});
	});
	var amount = 0;
	var html1  = "";
	var html2 = "";
	function rewriteDataHtml(content,type = "html1"){
		if(type =="html1"){
			html1 = content;
		}else{
			html2 = content;
		}
		$('.transfer-editor').summernote('code', html1 + " " + html2 + "<br> <br> <p> Lý do : </p>");
		//$('.note-editable').html(html1 + " " + html2);
		//$('textarea[name=content]').val(html1 + " " + html2);
	};
	$('#equipment_id_transfer').on('change', function() {
		var id = $(this).val();
		var action = $(this).closest('form').attr('data-filter');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				$("#amount-transfer").val("1");
				if(data.check == 'true') {
					amount = data.equipment.amount;
					title_transfer = data.equipment.title;
					rewriteDataHtml("Xin điều chuyển thiết bị từ "+data.equipment.equipment_department.title,"html1")
				}
			}
		});
	});
	$('#department_id_transfer').on('change', function() {
		var id = $(this).val();
		var action = $(this).closest('form').attr('data-filter');
		var token = $('input[name="_token"]').val();
		$.ajax({
			type:"POST",
			url:action,
			data: {
				'_token' : token,
				'id' : id,
			},
			success:function(data){
				if(data.check == 'true') {
					rewriteDataHtml("tới  "+data.department.title,"html2")
				}
			}
		});
	});
	$("#amount-transfer").on("change",function(){
		if($(this).val() != ""){
			var cur_amount = parseInt($(this).val());
			if(cur_amount > amount){
				var html = "";
				html +='<div class="modal fade" id="modal_transfer" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">';
				html +='<div class="modal-dialog modal-dialog-centered" role="document">';
				html +='<div class="modal-content">';
				html +='<div class="modal-header">';
				html +='<h5 class="modal-title" id="exampleModalLongTitle"> '+ title_transfer +' </h5>';
				html +='<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
				html +='<span aria-hidden="true">&times;</span>';
				html +='</button>';
				html +='</div>';
				html +='<div class="modal-body">';
				html +='Thiết bị ' + title_transfer + ' số lượng chỉ còn : ' + amount + ' , vui lòng nhập ít hơn ! ';
				html +='</div>';
				html +='<div class="modal-footer">';
				html +='<button type="button" class="btn btn-secondary" data-dismiss="modal">Trở về</button>';
				html +='</div>';
				html +='</div>';
				html +='</div>';
				html +='</div>';
				$("body").append(html);
				$("#modal_transfer").modal("show");
			}else{
			}
		}
	});
	$(".events .btn-action").click(function(e){
		e.preventDefault();
		$("#frm-action").attr('action',$(this).attr('href'));
		$("#frm-action").find('.action_id').attr('value',$(this).attr('data-id'));
		$("#frm-action").find('.code').attr('value',$(this).attr('code'));
		$("#frm-action").find('.type').attr('value',$(this).attr('type'));
		$("#frm-action").find('.author').attr('value',$(this).attr('user-id'));
		$("#frm-action").find('.note').attr('value',$(this).attr('note'));
		$("#editModal").modal('show');
		$('#editModal form')[0].reset();
	 });
	$("body").on('hidden.bs.modal',"#modal_transfer", function () {
		$('#modal_transfer').remove();
	  })
	$(".dev-form .btn-liqui").click(function(e){
		e.preventDefault();
		$("#frm-liqui").attr('action',$(this).attr('href'));
		$("#frm-liqui").find('.title-eq').attr('value',$(this).attr('title'));
		$("#frm-liqui").find('.amount').attr('value',$(this).attr('number'));
		$("#modal-liqui").modal('show');
		$('#modal-liqui form')[0].reset();
	 });
	$("#amount-eq").on("change",function(){
		if($(this).val() != ""){
			var number = $("#frm-liqui").find('.amount').val();
			var cur_amount = parseInt($(this).val());
			if(cur_amount > number){
			alert('Số lượng thiết bị chỉ còn : '+ number + '. Vui lòng nhập ít hơn !');
			}
		}
	});
	$(".btn-lqedit").click(function(e){
		e.preventDefault();
		$("#frm-liquitl").attr('action',$(this).attr('href'));
		$("#liquitl").modal('show');
		$('#liquitl form')[0].reset();
	 });
	$(".btn-repair").click(function(e){
		e.preventDefault();
		$("#corrected_form").attr('action',$(this).attr('href'));
		$("#modal_corrected").modal('show');
		$('#modal_corrected form')[0].reset();
	 });
	$(".hand").click(function(e){
		e.preventDefault();
		$("#hand_form").attr('action',$(this).attr('data-href'));
		$("#hand-title").attr('value',$(this).attr('data-title'));
		$("#modal_hand").modal('show');
		$('#modal_hand form')[0].reset();
	 });
	 $(".inactive").click(function(e){
		e.preventDefault();
		$("#inactive_form").attr('action',$(this).attr('data-href'));
		$("#modal_inactive").modal('show');
		$('#modal_inactive form')[0].reset();
	 });

	 $(".corrected").click(function(e){
		e.preventDefault();
		$("#corrected_form").attr('action',$(this).attr('data-href'));
		$("#modal_corrected").modal('show');
		$('#modal_corrected form')[0].reset();
	 });

	 $(".supplie_amount").click(function(e){
		e.preventDefault();
		$("#supplie_amount_form").attr('action',$(this).attr('data-href'));
		$("#supplie-title").attr('value',$(this).attr('data-title'));
		$("#modal_supplie_amount").modal('show');
		$('#modal_supplie_amount form')[0].reset();
	 });

	 $(".was_broken_mediacal").click(function(e){
		e.preventDefault();
		$("#was_broken_form").attr('action',$(this).attr('data-href'));
		$("#was_broken_form_title").attr('value',$(this).attr('data-title'));
		$("#modal_was_broken_mediacal").modal('show');
		$('#modal_was_broken_mediacal form')[0].reset();
	 });
	 $(".was_broken_device").click(function(e){
		e.preventDefault();
		$("#was_broken_device_form").attr('action',$(this).attr('data-href'));
		$("#modal_was_broken_device").modal('show');
		$('#modal_was_broken_device form')[0].reset();
	 });

	 $(".compatible_device").click(function(e){
		e.preventDefault();
		$("#compatible_show_form").attr('action',$(this).attr('data-href'));
		$("#compatible-title").attr('value',$(this).attr('data-title'));
		$("#modal_compatible_show").modal('show');
		$('#modal_compatible_show form')[0].reset();
	 });

	$(".compatible_device").click(function(e){
		e.preventDefault();
		$("#compatible_show_form").attr('action',$(this).attr('data-href'));
		$("#modal_compatible_show").modal('show');
		$('#modal_compatible_show form')[0].reset();
	 });

	$(".guarantee").click(function(e){
		e.preventDefault();
		$("#guarantee_show_form").attr('action',$(this).attr('data-href'));
		$("#guarantee_title").attr('value',$(this).attr('data-title'));
		$("#modal_guarantee_show").modal('show');
		$('#modal_guarantee_show form')[0].reset();
	 });

	 $(".ballot-success").click(function(e){
		e.preventDefault();
		$("#ballot_show_form").attr('action',$(this).attr('data-href'));
		$("#ballot_title").attr('value',$(this).attr('data-title'));
		$("#modal_ballot_show").modal('show');
		$('#modal_ballot_show form')[0].reset();
	 });


	 $(".accre-modal").click(function(e){
		e.preventDefault();
		$("#accre_show_form").attr('action',$(this).attr('data-href'));
		$("#accre_title").attr('value',$(this).attr('data-title'));
		$("#modal_accre_show").modal('show');
		$('#modal_accre_show form')[0].reset();
	 });

	 $(".accre-modal-update").click(function(e){
		e.preventDefault();
		$("#accre_show_form_update").attr('action',$(this).attr('data-href'));
		$("#accre_title_update").attr('value',$(this).attr('data-title'));
		$(".accre_provider_update").attr('value',$(this).attr('data-provider'));
		$(".card-block").html($(this).attr('data-content'));
		$("#modal_accre_show_update").modal('show');
		$('#modal_accre_show_update form')[0].reset();
	 });


	 $(".guarantee-modal-update").click(function(e){
		e.preventDefault();
		$("#guarantee_show_form_update").attr('action',$(this).attr('data-href'));
		$("#guarantee_title_update").attr('value',$(this).attr('data-title'));
		$(".guarantee_provider_update").attr('value',$(this).attr('data-provider'));
		$(".card-block").html($(this).attr('data-content'));
		$("#modal_guarantee_show_update").modal('show');
		$('#modal_guarantee_show_update form')[0].reset();
	 });





	 $('.status-color').each(function(){
		var item = $(this).find('.btn-status');
		var value = $(this).find('.btn-status').html();
		if(value == "Chưa xử lý" || value == "Chờ thanh lý" || value == "Chưa duyệt" ) {
		   item.addClass('pendding');
		}
		else if(value == "Đã xử lý" || value == "Đã thanh lý" || value == "Đã duyệt" ) {
		   item.addClass('public');
		}
		else if(value == "Hủy") {
		   item.addClass('cancel');
		}
		else {
		}
	 });

	 $('#equiment-form-filter').on('change', function() {                     
		$(this).submit();
	});
	  $('#dashboard').on('change', function() {                     
		$(this).submit();
	});

	if($('#file-chosen').length > 0 && $('#actual-btn').length > 0) {
		const actualBtn = document.getElementById('actual-btn');
		const fileChosen = document.getElementById('file-chosen');
		actualBtn.addEventListener('change', function(){
			fileChosen.textContent = this.files[0].name
		});

	};
    //show notify
	$("header").on('click','.bell .toggle-bell',function(){		
		$("header .bell .dropdown-bell").slideToggle();
	});
    $(document).mouseup(function(e) {
        var container = $(".bell");
        if (!container.is(e.target) && container.has(e.target).length === 0 ) 
        {    
            $(".bell .dropdown-bell").slideUp();
        }
    });
    $('input.format-number').keyup(function(event) {
	  // skip for arrow keys
	  if(event.which >= 37 && event.which <= 40) return;
	  // format number
	  $(this).val(function(index, value) {
	    return value
	    .replace(/\D/g, "")
	    .replace(/\B(?=(\d{3})+(?!\d))/g, ",")
	    ;
	  });
	});
   /* $("input.format-number").on("keyup",function(){
    	var n = parseInt(this.value.replace(/\D/g,''),10);
        $(this).val(addCommas(n));
    });
   
    function addCommas(nStr)
    {
        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }*/
    $(".dropdown-avatar").on('click', '.avatar', function() {
        $(this).siblings('.dropdown-profile').addClass('show');
    });
    $(document).mouseup(function(e) {
        var container = $(".dropdown-avatar");
        if (!container.is(e.target) && container.has(e.target).length === 0 ) 
        {    
            $(".dropdown-avatar .dropdown-profile").removeClass("show")
        }
    });
    $(".btn-change-pass").click(function(e){
		e.preventDefault();
		$("#frmSumitChangePass").attr('action',$(this).attr('href'));
		$("#mdlCustom").modal('show');
		$('#mdlCustom form')[0].reset();
	});
});
(function($) {
	'use strict';
	if($('.check__checkbox_all').length > 0) {
	}
});