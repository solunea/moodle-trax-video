<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Trax Video for Moodle.
 *
 * @package    mod_traxvideo
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Plugin strings.
$string['modulename'] = 'Trax Video';
$string['modulename_help'] = "Ce plugin permet d'ajouter dans vos cours des vidéos qui émettent des informations xAPI.";
$string['modulenameplural'] = 'Trax Videos';
$string['pluginadministration'] = 'Trax Video administration';
$string['pluginname'] = 'Trax Video';
$string['page-mod-traxvideo-x'] = 'Une page Trax Video';
$string['page-mod-traxvideo-view'] = 'Page principale Trax Video';

// Permissions.
$string['traxvideo:addinstance'] = 'Ajouter une nouvelle vidéo Trax Video';
$string['traxvideo:view'] = 'Visionner une vidéo Trax Video';

// Mod Form.
$string['poster'] = 'Poster (PNG,JPG)';
$string['poster_help'] = 'Renseigner l\'URL du poster de la vidéo, qui sera affiché avant la demande de lecture de la vidéo.';
$string['sourcemp4'] = 'Video (URL de la vidéo)';
$string['sourcemp4_help'] = 'Renseigner l\'URL de la vidéo.';

// Privacy metadata.
$string['privacy:metadata'] = 'Ce plugin n\'enregistre pas de données personnelles.
    Toutefois, certains événements peuvent être envoyés au plugin Trax Logs.
    Veuillez vous référer à la politique de sécurité des données du plugin Trax Logs.';

$string['configdisplayoptions'] = 'Sélectionnez dans cette liste les options qui doivent être présentées pour les nouvelles vidéos. Maintenir la touche CTRL pour sélectionner plusieurs champs.';
$string['configframesize'] = 'Lorsqu\'une vidéo est présentée dans une frame, définissez la hauteur en pixels de la frame principale qui contient la navigation.';
$string['configparametersettings'] = 'La valeur par défaut pour le panneau des paramètres dans le formulaire des nouvelles vidéos.';
$string['configpopup'] = 'Lorsque \'une nouvelle vidéo est présentée dans une fenêtre popup, cette option doit-elle présentée par défaut ?';
$string['configpopupheight'] = 'La hauteur par défaut des fenêtres pop-up, en pixels.';
$string['configpopupwidth'] = 'La largeur par défaut des fenêtres pop-up, en pixels.';
$string['displayoptions'] = 'Options d\'affichage disponibles.';
$string['displayselect'] = 'Affichage';
$string['displayselect_help'] = 'Ce paramètre détermine comment la vidéo doit être affichée :

* Automatique - Sélection automatique du meilleur format d\'affichage
* Intégrer - La vidéo est affichée dans la page Moodle courante, avec les menus de navigation dans Moodle et le reste du cours.
* Dans une fenêtre surgissante - La vidéo est présentée dans une nouvelle fenêtre pop-up, à la dimension demandée.
* Ouvrir - La vidéo est présentée en plein écran dans la fenêtre courante. La navigation dans Moodle et le reste du cours est masquée.
* Nouvelle fenêtre -  La vidéo est présentée en plein écran dans une nouvelle fenêtre du navigateur. La navigation dans Moodle et le reste du cours est masquée.';
$string['displayselect_link'] = 'mod/file/mod';
$string['displayselectexplain'] = 'Choisir le type d\'affichage.';
$string['popupheight'] = 'Hauteur du pop-up (en pixels)';
$string['popupheightexplain'] = 'La hauteur de la fenêtre pop-up, en pixels.';
$string['popupwidth'] = 'Largeur du pop-up (en pixels)';
$string['popupwidthexplain'] = 'La largeur de la fenêtre pop-up, en pixels.';

$string['click'] = 'Cliquez sur le lien';
$string['videolink'] = 'pour afficher la vidéo.';

$string['videoIsTerminated'] = 'La vidéo est terminée. Vous pouvez retourner dans le cours.';
$string['terminateVideo'] = 'Terminer la vidéo.';

$string['terminateSelect'] = 'Envoi de l\'état Terminé';
$string['terminateSelect_help'] = 'Configuration de l\'envoi du statement xAPI "Terminated" :

* Sur demande de l\'apprenant - Un bouton permet de déclarer la vidéo terminée à tout moment. Une fois cliqué, le statement "Terminated" est envoyé, et la vidéo est masquée.
* À complétion, puis arrêt -  Une fois la lecture de la vidéo complétée, le statement "Terminated" est envoyé, et la vidéo est masquée.
* À complétion, sans arrêt - Une fois la lecture de la vidéo complétée, le statement "Terminated" est envoyé, mais la vidéo n\'est pas masquée et peut continuer à être lue. En revanche, quelles que soient les actions sur la vidéo, plus aucun statement ne sera envoyé.
';
$string['terminateOnAction'] = 'Sur demande de l\'apprenant';
$string['terminateOnCompleteStop'] = 'À complétion, puis arrêt';
$string['terminateOnCompleteContinue'] = 'À complétion, sans arrêt';

