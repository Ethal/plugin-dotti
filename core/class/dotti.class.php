<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class dotti extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'dotti_update';
		$return['progress_file'] = '/tmp/dependancy_dotti_in_progress';
		if (exec('which hcitool | wc -l') != 0) {
			$return['state'] = 'ok';
		} else {
			$return['state'] = 'nok';
		}
		return $return;
	}

	public static function dependancy_install() {
		log::remove('dotti_update');
		$cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
		$cmd .= ' >> ' . log::getPathToLog('dotti_update') . ' 2>&1 &';
		exec($cmd);
	}

	public static function text2array($_text, $_color = 'FFFFFF', $_displaySize = array(8, 8)) {
		$image = imagecreatetruecolor($_displaySize[0] + 1, $_displaySize[1] + 1);
		$rgbcolor = hex2rgb($_color);
		imagefill($image, 0, 0, 0x000000);
		imagestring($image, 1, 0, 0, $_text, 0xFFFFFF);
		$return = array();
		for ($x = 0; $x < imagesy($image); $x++) {
			for ($y = 0; $y < imagesx($image); $y++) {
				if (imagecolorat($image, $y, $x) != 0) {
					$return[$x][$y] = array($rgbcolor[0], $rgbcolor[1], $rgbcolor[2]);
				} else {
					$return[$x][$y] = array(0, 0, 0);
				}
			}
		}
		$column_black = true;
		foreach ($return as $x => $line) {
			if ($line[0][0] != 0 || $line[0][0] != 0 || $line[0][0] != 0) {
				$column_black = false;
				break;
			}
		}
		foreach ($return as $x => &$line) {
			if ($column_black) {
				array_shift($line);
			} else {
				array_pop($line);
			}
		}
		array_pop($return);
		return $return;
	}

	public static function number2line($_number, $_line = 1) {
		$return = array();
		$colors = array(
			1000 => '#FF0000',
			100 => '#FFFF00',
			10 => '#00FF00',
			1 => '#FFFFFF',
		);
		$start = ($_line - 1) * 8 + 1;
		$i = 0;
		for ($j = 0; $j < 8; $j++) {
			$return[$start + $j] = array(0, 0, 0);
		}
		foreach ($colors as $key => $color) {
			if (($_number / $key) >= 1) {
				for ($j = 1; $j <= ($_number / $key); $j++) {
					$return[$start + $i] = hex2rgb($color);
					if ($i == 8) {
						break (2);
					}
					$i++;
				}
				$_number = $_number - (round($_number / $key, 0, PHP_ROUND_HALF_DOWN) * $key);
			}
		}
		return $return;
	}

	public static function array2table($_array) {
		$return = '<table>';
		foreach ($_array as $x => $line) {
			$return .= '<tr>';
			foreach ($line as $y => $color) {
				$return .= '<td style="Background-Color:RGB(' . $color[0] . ',' . $color[1] . ',' . $color[2] . ');height:40px;width:40px;"></td>';
			}
			$return .= '</tr>';
		}
		$return .= '<table>';
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	public function postSave() {
		$cmd = $this->getCmd(null, 'sendtext');
		if (!is_object($cmd)) {
			$cmd = new dottiCmd();
			$cmd->setLogicalId('sendtext');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Afficher text', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('message');
		$cmd->setEqLogic_id($this->getId());
		$cmd->setDisplay('title_placeholder', __('Options', __FILE__));
		$cmd->save();

		$cmd = $this->getCmd(null, 'blackscreen');
		if (!is_object($cmd)) {
			$cmd = new dottiCmd();
			$cmd->setLogicalId('blackscreen');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Ecran noir', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('other');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'rownumber');
		if (!is_object($cmd)) {
			$cmd = new dottiCmd();
			$cmd->setLogicalId('rownumber');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Nombre en ligne', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('message');
		$cmd->setDisplay('title_placeholder', __('Ligne', __FILE__));
		$cmd->setDisplay('message_placeholder', __('Nombre', __FILE__));
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'loadid');
		if (!is_object($cmd)) {
			$cmd = new dottiCmd();
			$cmd->setLogicalId('loadid');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Charger Image', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('message');
		$cmd->setDisplay('title_disable', 1);
		$cmd->setDisplay('message_placeholder', __('ID (0 à 255)', __FILE__));
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'saveid');
		if (!is_object($cmd)) {
			$cmd = new dottiCmd();
			$cmd->setLogicalId('saveid');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Sauver Image', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('message');
		$cmd->setDisplay('title_disable', 1);
		$cmd->setDisplay('message_placeholder', __('ID (0 à 255)', __FILE__));
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'sendraw');
		if (!is_object($cmd)) {
			$cmd = new dottiCmd();
			$cmd->setLogicalId('sendraw');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Afficher', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('message');
		$cmd->setEqLogic_id($this->getId());
		$cmd->setDisplay('title_placeholder', __('Options', __FILE__));
		$cmd->setDisplay('message_placeholder', __('Données brute en json', __FILE__));
		$cmd->save();
	}

	public function generateJson($_data, $_options = array()) {
		$file = '/tmp/dotti' . str_replace(':', '', $this->getConfiguration('mac')) . '.json';
		$data = array();
		$i = 1;
		if (isset($_data[0]) && is_array($_data[0])) {
			foreach ($_data as $x => $line) {
				foreach ($line as $y => $color) {
					$data[$i] = $color;
					$i++;
				}
			}
		} else {
			$data = $_data;
		}
		$_options['data'] = $data;
		if (file_exists($file)) {
			shell_exec('sudo rm ' . $file);
		}
		file_put_contents($file, json_encode($_options, JSON_FORCE_OBJECT));
	}

	public function sendData($_data, $_options = array()) {
		$this->generateJson($_data, $_options);
		$cmd = 'sudo python ' . dirname(__FILE__) . '/../../resources/dottiset.py ' . $this->getConfiguration('mac') . ' 2>&1';
		$result = shell_exec($cmd);
		if (trim($result) != 'OK') {
			$result = shell_exec($cmd);
		}
		if (trim($result) != 'OK') {
			throw new Exception('[Dotti] ' . $result);
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

class dottiCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = array()) {
		$eqLogic = $this->getEqLogic();
		$logicalId = $this->getLogicalId();
		if ($logicalId == 'sendtext') {
			$options = arg2array($_options['title']);
			if (!isset($options['color'])) {
				$options['color'] = 'FFFFFF';
			}
			$eqLogic->sendData(dotti::text2array($_options['message'], $options['color']));
		}
		if ($logicalId == 'blackscreen') {
			$data = array();
			for ($i = 1; $i < 65; $i++) {
				$data[$i] = array(0, 0, 0);
			}
			$eqLogic->sendData($data);
		}
		if ($logicalId == 'rownumber') {
			if (!is_numeric($_options['message'])) {
				throw new Exception(__('Le champs message doit être un numérique : ', __FILE__) . $_options['message']);
			}
			$line = 1;
			if (is_numeric($_options['title'])) {
				$line = $_options['title'];
			} else {
				$options = arg2array($_options['title']);
				if (!isset($options['line'])) {
					$line = $options['line'];
				}
			}
			$eqLogic->sendData(dotti::number2line($_options['message'], $line));
		}
		if (in_array($logicalId, array('loadid', 'saveid'))) {
			$data = array();
			$data['type'] = $logicalId;
			$data['id'] = $_options['message'];
			$eqLogic->sendData($data);
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
