<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title></title>
  <style>
    body {
        background-color: #fff;
        overflow: hidden;
        text-align: center;
       }

       body,
       html {
        height: 100%;
        width: 100%;
        margin: 0;
        padding: 0;
       }

       #animationWindow {
        width: 100%;
        height: 100%;
       }

       span{
        color:#222;
        font-family:sans-serif, 'Roboto';
        font-size:23px;
       }
  </style>

</head>
<body>
<!-- partial:index.partial.html -->
{{-- <div id="animationWindow"> --}}

<span style="
/* position: absolute; */
/* bottom: 20%; */
/* left: 0; */
/* right: 0; */
/* font-size: 23px; */
height: 100vh;
/* font-weight: bold; */
display: flex;
">
<div style="display: flex; justify-content: center; flex-direction: column; width: 100%; /* text-align: center; */ align-items: center">
@if($msg == 'unpaid')
{{-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> --}}
    <svg id="Capa_1" enable-background="new 0 0 512 512" height="312" viewBox="0 0 512 512" width="312" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="256" x2="256" y1="512" y2="0"><stop offset="0" stop-color="#fd3a84"/><stop offset="1" stop-color="#ffa68d"/></linearGradient><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="301" x2="301" y1="271" y2="61"><stop offset="0" stop-color="#ffc2cc"/><stop offset="1" stop-color="#fff2f4"/></linearGradient><g><path d="m508.993 156.991c-2.833-3.772-7.276-5.991-11.993-5.991h-107.257c-7.163-42.511-44.227-75-88.743-75s-81.58 32.489-88.743 75h-93.235l-19.6-138.107c-1.049-7.396-7.38-12.893-14.851-12.893h-69.571c-8.284 0-15 6.716-15 15s6.716 15 15 15h56.55l19.599 138.104v.001.003l22.643 159.499c2.457 17.197 10.82 32.978 23.598 44.684-10.004 8.26-16.39 20.753-16.39 34.709 0 20.723 14.085 38.209 33.181 43.414-2.044 5.137-3.181 10.73-3.181 16.586 0 24.813 20.187 45 45 45s45-20.187 45-45c0-5.258-.915-10.305-2.58-15h125.16c-1.665 4.695-2.58 9.742-2.58 15 0 24.813 20.187 45 45 45s45-20.187 45-45-20.187-45-45-45h-240c-8.271 0-15-6.729-15-15s6.729-15 15-15h224.742c33.309 0 62.963-22.368 72.098-54.339l48.567-167.483c1.313-4.531.419-9.416-2.414-13.187z" fill="url(#SVGID_1_)"/><g><g><path d="m301 61c-57.897 0-105 47.103-105 105s47.103 105 105 105 105-47.103 105-105-47.103-105-105-105zm31.82 115.607c5.858 5.858 5.858 15.355 0 21.213-5.859 5.858-15.355 5.857-21.213 0l-10.607-10.607-10.607 10.607c-5.858 5.858-15.356 5.857-21.213 0-5.858-5.857-5.858-15.355 0-21.213l10.607-10.607-10.607-10.607c-5.858-5.858-5.858-15.355 0-21.213s15.355-5.857 21.213 0l10.607 10.607 10.607-10.607c5.857-5.858 15.355-5.858 21.213 0 5.858 5.857 5.858 15.355 0 21.213l-10.607 10.607z" fill="url(#SVGID_2_)"/></g></g></g></svg>
    <span style="font-size: xx-large;text-transform: capitalize;margin-top:40px">Order belum dibayar</span>
@elseif ($msg == 'paid')
    <svg id="Capa_1" enable-background="new 0 0 512 512" height="312" viewBox="0 0 512 512" width="312" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="256" x2="256" y1="512" y2="0"><stop offset="0" stop-color="#00b59c"/><stop offset="1" stop-color="#9cffac"/></linearGradient><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="301" x2="301" y1="271" y2="61"><stop offset="0" stop-color="#c3ffe8"/><stop offset=".9973" stop-color="#f0fff4"/></linearGradient><g><path d="m508.993 156.991c-2.833-3.772-7.276-5.991-11.993-5.991h-107.257c-7.163-42.511-44.227-75-88.743-75s-81.58 32.489-88.743 75h-93.235l-19.6-138.107c-1.049-7.396-7.38-12.893-14.851-12.893h-69.571c-8.284 0-15 6.716-15 15s6.716 15 15 15h56.55l19.599 138.104v.001.003l22.643 159.499c2.457 17.197 10.82 32.978 23.598 44.684-10.004 8.26-16.39 20.753-16.39 34.709 0 20.723 14.085 38.209 33.181 43.414-2.045 5.137-3.181 10.73-3.181 16.586 0 24.813 20.187 45 45 45s45-20.187 45-45c0-5.258-.915-10.305-2.58-15h125.16c-1.665 4.695-2.58 9.742-2.58 15 0 24.813 20.187 45 45 45s45-20.187 45-45-20.187-45-45-45h-240c-8.271 0-15-6.729-15-15s6.729-15 15-15h224.742c33.309 0 62.963-22.368 72.098-54.339l48.567-167.483c1.313-4.531.419-9.416-2.414-13.187z" fill="url(#SVGID_1_)"/><g><g><path d="m301 61c-57.897 0-105 47.103-105 105s47.103 105 105 105 105-47.103 105-105-47.103-105-105-105zm40.606 100.607-45 45c-2.928 2.929-6.767 4.393-10.606 4.393s-7.678-1.464-10.606-4.394l-15-15c-5.858-5.858-5.858-15.355 0-21.213 5.857-5.858 15.355-5.858 21.213 0l4.394 4.393 34.394-34.393c5.857-5.858 15.355-5.858 21.213 0 5.857 5.859 5.857 15.356-.002 21.214z" fill="url(#SVGID_2_)"/></g></g></g></svg>
    <span style="font-size: xx-large;text-transform: capitalize;margin-top:40px">Order sudah dibayar</span>
@elseif ($msg == 'unfinish')
    <svg id="Capa_1" enable-background="new 0 0 512 512" height="312" viewBox="0 0 512 512" width="312" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="256" x2="256" y1="512" y2="0"><stop offset="0" stop-color="#fd3a84"/><stop offset="1" stop-color="#ffa68d"/></linearGradient><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="301" x2="301" y1="271" y2="61"><stop offset="0" stop-color="#ffc2cc"/><stop offset="1" stop-color="#fff2f4"/></linearGradient><g><path d="m508.993 156.991c-2.833-3.772-7.276-5.991-11.993-5.991h-107.257c-7.163-42.511-44.227-75-88.743-75s-81.58 32.489-88.743 75h-93.235l-19.6-138.107c-1.049-7.396-7.38-12.893-14.851-12.893h-69.571c-8.284 0-15 6.716-15 15s6.716 15 15 15h56.55l19.599 138.104v.001.003l22.643 159.499c2.457 17.197 10.82 32.978 23.598 44.684-10.004 8.26-16.39 20.753-16.39 34.709 0 20.723 14.085 38.209 33.181 43.414-2.044 5.137-3.181 10.73-3.181 16.586 0 24.813 20.187 45 45 45s45-20.187 45-45c0-5.258-.915-10.305-2.58-15h125.16c-1.665 4.695-2.58 9.742-2.58 15 0 24.813 20.187 45 45 45s45-20.187 45-45-20.187-45-45-45h-240c-8.271 0-15-6.729-15-15s6.729-15 15-15h224.742c33.309 0 62.963-22.368 72.098-54.339l48.567-167.483c1.313-4.531.419-9.416-2.414-13.187z" fill="url(#SVGID_1_)"/><g><g><path d="m301 61c-57.897 0-105 47.103-105 105s47.103 105 105 105 105-47.103 105-105-47.103-105-105-105zm31.82 115.607c5.858 5.858 5.858 15.355 0 21.213-5.859 5.858-15.355 5.857-21.213 0l-10.607-10.607-10.607 10.607c-5.858 5.858-15.356 5.857-21.213 0-5.858-5.857-5.858-15.355 0-21.213l10.607-10.607-10.607-10.607c-5.858-5.858-5.858-15.355 0-21.213s15.355-5.857 21.213 0l10.607 10.607 10.607-10.607c5.857-5.858 15.355-5.858 21.213 0 5.858 5.857 5.858 15.355 0 21.213l-10.607 10.607z" fill="url(#SVGID_2_)"/></g></g></g></svg>
    <span style="font-size: xx-large;text-transform: capitalize;margin-top:40px">Proses pembayaran gagal</span>
@elseif ($msg == 'failed')
    <svg id="Capa_1" enable-background="new 0 0 512 512" height="312" viewBox="0 0 512 512" width="312" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="256" x2="256" y1="512" y2="0"><stop offset="0" stop-color="#fd3a84"/><stop offset="1" stop-color="#ffa68d"/></linearGradient><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="301" x2="301" y1="271" y2="61"><stop offset="0" stop-color="#ffc2cc"/><stop offset="1" stop-color="#fff2f4"/></linearGradient><g><path d="m508.993 156.991c-2.833-3.772-7.276-5.991-11.993-5.991h-107.257c-7.163-42.511-44.227-75-88.743-75s-81.58 32.489-88.743 75h-93.235l-19.6-138.107c-1.049-7.396-7.38-12.893-14.851-12.893h-69.571c-8.284 0-15 6.716-15 15s6.716 15 15 15h56.55l19.599 138.104v.001.003l22.643 159.499c2.457 17.197 10.82 32.978 23.598 44.684-10.004 8.26-16.39 20.753-16.39 34.709 0 20.723 14.085 38.209 33.181 43.414-2.044 5.137-3.181 10.73-3.181 16.586 0 24.813 20.187 45 45 45s45-20.187 45-45c0-5.258-.915-10.305-2.58-15h125.16c-1.665 4.695-2.58 9.742-2.58 15 0 24.813 20.187 45 45 45s45-20.187 45-45-20.187-45-45-45h-240c-8.271 0-15-6.729-15-15s6.729-15 15-15h224.742c33.309 0 62.963-22.368 72.098-54.339l48.567-167.483c1.313-4.531.419-9.416-2.414-13.187z" fill="url(#SVGID_1_)"/><g><g><path d="m301 61c-57.897 0-105 47.103-105 105s47.103 105 105 105 105-47.103 105-105-47.103-105-105-105zm31.82 115.607c5.858 5.858 5.858 15.355 0 21.213-5.859 5.858-15.355 5.857-21.213 0l-10.607-10.607-10.607 10.607c-5.858 5.858-15.356 5.857-21.213 0-5.858-5.857-5.858-15.355 0-21.213l10.607-10.607-10.607-10.607c-5.858-5.858-5.858-15.355 0-21.213s15.355-5.857 21.213 0l10.607 10.607 10.607-10.607c5.857-5.858 15.355-5.858 21.213 0 5.858 5.857 5.858 15.355 0 21.213l-10.607 10.607z" fill="url(#SVGID_2_)"/></g></g></g></svg>
    <span style="font-size: xx-large;text-transform: capitalize;margin-top:40px">Proses pembayaran gagal</span>
@endif
</div>
{{-- </div> --}}
<!-- partial -->
  <script src='https://cdnjs.cloudflare.com/ajax/libs/bodymovin/4.8.0/bodymovin.min.js'></script><script  src="./script.js"></script>
{{-- <script>
    var animData = {
		wrapper: document.querySelector('#animationWindow'),
		animType: 'svg',
		loop: true,
		prerender: true,
		autoplay: true,
		path: 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/35984/LEGO_loader.json'
	};
	var anim = bodymovin.loadAnimation(animData);
anim.setSpeed(3.4);
</script> --}}
</body>
</html>
