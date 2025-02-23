<?php
    
    define( '_USERAGENT', 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/48 (like Gecko) Safari/48' );
    define( '_TMP', 'tmp/' );
    define( '_DIR_TMP', str_replace('\\', '/', getcwd()) . '/' . _TMP );
    define( '_BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . str_replace( basename( $_SERVER['SCRIPT_NAME'] ) , '', $_SERVER['SCRIPT_NAME'] ) . _TMP );
    
    function removePontuacao( $numero )
    {
        return str_replace( array('.','-','/'), '', $numero );
    }
    
    function getImgCaptchaRF()
    {
        $url = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/captcha/gerarCaptcha.asp';
 
        $path_cookie = tempnam( _DIR_TMP, 'rf' );
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $path_cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $path_cookie);
        $resp = curl_exec($ch);
        curl_close($ch);
        
        $nome_img = 'img'.uniqid().'.png';
        $arquivo = fopen( _DIR_TMP . $nome_img, "w" );
        fwrite( $arquivo, $resp );
        fclose( $arquivo );
        
        $return['cookie']   = $path_cookie;
        $return['img']      = $nome_img;
        
        return $return;
    }
    
    if( isset($_POST['submit']) )
    {
        $cnpj    = removePontuacao( $_POST['inp_cnpj'] );
        $captcha = $_POST['captcha'];
        $cookie  = $_POST['cookie'];
        
        $url = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp';
        $ch = curl_init($url);
 
        $post_data = 'origem=comprovante&cnpj='.$cnpj.'&txtTexto_captcha_serpro_gov_br='.$captcha.'&submit1=Consultar&search_type=cnpj';
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        $resp = curl_exec($ch);
        curl_close($ch);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        $resp2 = curl_exec($ch);
        curl_close($ch);
        
        echo $resp2;
    }
    else
    {
        $r = getImgCaptchaRF();
?>
 
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>Consulta CNPJ</title>              
            </head>
 
            <body>
 
                <form name="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="text" name="inp_cnpj" placeholder="Numero do CNPJ" /><br>
                    <img src="<?php echo _BASE_URL . $r['img'] ?>" /><br>
                    <input type="text" name="captcha" placeholder="Captcha" /><br><hr>
                    <input type="hidden" name="cookie" value="<?php echo $r['cookie'] ?>" />
                    <input type="submit" name="submit" value="Enviar" />
                </form>
 
            </body>
        </html>
<?php
    }
?>