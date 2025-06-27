<?php
/**
 * Esempio di utilizzo dell'integrazione Cohesion
 * 
 * Questo file mostra come utilizzare il plugin Integrazione Cohesion
 * in una pagina WordPress personalizzata.
 */

// Questo codice va inserito in un template WordPress o in una pagina

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Integrazione Cohesion</title>
    <?php wp_head(); ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        
        .cohesion-example {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .cohesion-user-info {
            background: #e8f5e8;
            border: 1px solid #4CAF50;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .cohesion-profile-data {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
        }
        
        .cohesion-login-section {
            text-align: center;
            background: #f0f8ff;
            border: 1px solid #0073aa;
            border-radius: 5px;
            padding: 30px;
            margin: 20px 0;
        }
        
        .button {
            background: #0073aa;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 3px;
            display: inline-block;
            margin: 5px;
        }
        
        .button:hover {
            background: #005a87;
            color: white;
        }
        
        .button.logout {
            background: #dc3232;
        }
        
        .button.logout:hover {
            background: #a82727;
        }
    </style>
</head>
<body>
    <h1>Test Integrazione Cohesion</h1>
    
    <div class="cohesion-example">
        <h2>Stato Autenticazione</h2>
        
        <?php if (is_user_logged_in()): ?>
            <?php 
            $current_user = wp_get_current_user();
            $user_manager = new CohesionUserManager();
            $is_cohesion_user = $user_manager->is_cohesion_user($current_user->ID);
            $cohesion_profile = $user_manager->get_user_cohesion_profile($current_user->ID);
            $fiscal_code = $user_manager->get_user_fiscal_code($current_user->ID);
            ?>
            
            <div class="cohesion-user-info">
                <h3>✅ Utente autenticato</h3>
                <p><strong>Nome:</strong> <?php echo esc_html($current_user->display_name); ?></p>
                <p><strong>Email:</strong> <?php echo esc_html($current_user->user_email); ?></p>
                <p><strong>Username:</strong> <?php echo esc_html($current_user->user_login); ?></p>
                <p><strong>Ruolo:</strong> <?php echo esc_html(implode(', ', $current_user->roles)); ?></p>
                <p><strong>Autenticato via Cohesion:</strong> <?php echo $is_cohesion_user ? '✅ Sì' : '❌ No'; ?></p>
                
                <?php if ($fiscal_code): ?>
                    <p><strong>Codice Fiscale:</strong> <?php echo esc_html($fiscal_code); ?></p>
                <?php endif; ?>
                
                <?php if ($is_cohesion_user && !empty($cohesion_profile)): ?>
                    <h4>Dati dal profilo Cohesion:</h4>
                    <div class="cohesion-profile-data"><?php 
                        echo esc_html(print_r($cohesion_profile, true)); 
                    ?></div>
                <?php endif; ?>
                
                <p>
                    <a href="<?php echo home_url('/cohesion/logout'); ?>" class="button logout">
                        Logout Cohesion
                    </a>
                    <a href="<?php echo wp_logout_url(); ?>" class="button logout">
                        Logout WordPress
                    </a>
                </p>
            </div>
            
        <?php else: ?>
            
            <div class="cohesion-login-section">
                <h3>❌ Utente non autenticato</h3>
                <p>Effettua il login per testare l'integrazione Cohesion</p>
                
                <p>
                    <a href="<?php echo home_url('/cohesion/login'); ?>" class="button">
                        🔐 Login con Cohesion
                    </a>
                    <a href="<?php echo wp_login_url(); ?>" class="button">
                        🔑 Login WordPress
                    </a>
                </p>
                
                <p><small>
                    Il login Cohesion supporta SPID, CIE, eIDAS e sistemi di autenticazione della Regione Marche
                </small></p>
            </div>
            
        <?php endif; ?>
    </div>
    
    <div class="cohesion-example">
        <h2>Shortcode di esempio</h2>
        
        <h3>Shortcode login:</h3>
        <code>[cohesion_login button_text="Accedi con SPID" show_spid="true"]</code>
        <div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin: 10px 0;">
            <?php echo do_shortcode('[cohesion_login button_text="Accedi con SPID" show_spid="true"]'); ?>
        </div>
        
        <h3>Shortcode logout:</h3>
        <code>[cohesion_logout button_text="Esci dal sistema"]</code>
        <div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin: 10px 0;">
            <?php echo do_shortcode('[cohesion_logout button_text="Esci dal sistema"]'); ?>
        </div>
    </div>
    
    <div class="cohesion-example">
        <h2>URL di test</h2>
        <ul>
            <li><strong>Login:</strong> <a href="<?php echo home_url('/cohesion/login'); ?>" target="_blank"><?php echo home_url('/cohesion/login'); ?></a></li>
            <li><strong>Logout:</strong> <a href="<?php echo home_url('/cohesion/logout'); ?>" target="_blank"><?php echo home_url('/cohesion/logout'); ?></a></li>
            <li><strong>Callback:</strong> <?php echo home_url('/cohesion/callback'); ?> <em>(utilizzato da Cohesion)</em></li>
        </ul>
    </div>
    
    <div class="cohesion-example">
        <h2>Configurazione attuale</h2>
        <ul>
            <li><strong>ID Sito:</strong> <?php echo esc_html(get_option('cohesion_id_sito', 'TEST')); ?></li>
            <li><strong>SAML 2.0:</strong> <?php echo get_option('cohesion_enable_saml20', true) ? '✅ Abilitato' : '❌ Disabilitato'; ?></li>
            <li><strong>SPID:</strong> <?php echo get_option('cohesion_enable_spid', true) ? '✅ Abilitato' : '❌ Disabilitato'; ?></li>
            <li><strong>CIE:</strong> <?php echo get_option('cohesion_enable_cie', true) ? '✅ Abilitato' : '❌ Disabilitato'; ?></li>
            <li><strong>eIDAS:</strong> <?php echo get_option('cohesion_enable_eidas', false) ? '✅ Abilitato' : '❌ Disabilitato'; ?></li>
            <li><strong>Creazione automatica utenti:</strong> <?php echo get_option('cohesion_auto_create_users', true) ? '✅ Abilitata' : '❌ Disabilitata'; ?></li>
            <li><strong>Ruolo predefinito:</strong> <?php echo esc_html(get_option('cohesion_default_role', 'subscriber')); ?></li>
        </ul>
        
        <p>
            <a href="<?php echo admin_url('options-general.php?page=cohesion-settings'); ?>" class="button">
                ⚙️ Modifica Configurazione
            </a>
        </p>
    </div>
    
    <div class="cohesion-example">
        <h2>Note per i test</h2>
        <ul>
            <li>Assicurati di aver configurato l'<strong>ID Sito</strong> nelle impostazioni</li>
            <li>Per i test puoi utilizzare l'ID Sito "TEST"</li>
            <li>Per produzione devi richiedere un ID Sito ufficiale alla Regione Marche</li>
            <li>Il plugin supporta SPID, CIE e altri sistemi di identità digitale</li>
            <li>Gli utenti vengono creati automaticamente al primo login (se abilitato)</li>
            <li>I dati del profilo Cohesion vengono memorizzati nei metadati dell'utente</li>
        </ul>
    </div>
    
    <?php wp_footer(); ?>
</body>
</html>
