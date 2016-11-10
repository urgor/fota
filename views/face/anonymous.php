Кто ты?
<form class="formLogin" action="/authentificate/login" method="POST" autocomplete="off" onsubmit="authHandler.formLogin(); return false;" >
	<input type="text" name="email" size="15" hint="логин" class="hinting"/>
	<input type="password" name="password" size="15" hint="пароль" class="hinting password" />
	<div class="center"><input type="submit" value="" id="button_login" title="Login" onclick="authHandler.formLogin(); return false;" /></div>
</form>