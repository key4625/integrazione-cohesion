<?php
/**
 * Script per forzare il flush delle regole di rewrite
 */

// Carica WordPress
require_once __DIR__ . '/../../../wp-config.php';

// Forza il flush delle regole
flush_rewrite_rules();

// Aggiorna la versione delle regole
update_option('cohesion_rewrite_rules_version', '0');

echo "✅ Regole di rewrite flushed\n";
echo "✅ Versione regole resettata\n";
echo "🔄 Le regole verranno rigenerate al prossimo caricamento\n";
?>
