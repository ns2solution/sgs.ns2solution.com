const showNotif = (type, msg) => {
	switch(type){
		case 'error':
		return $('#notification').stop().css('background', '#EE5E64').empty().append(`<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>&nbsp; ` + msg).hide().slideDown().delay(2000).slideUp('fast');
		break;

		case 'success':
		return $('#notification').stop().css('background', '#02B799').empty().append(`<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>&nbsp; ` + msg).hide().slideDown().delay(2000).slideUp('fast');
		break;
	}
}

const closeNotif = () => {
	return $('#notification').stop().slideUp('fast');
}