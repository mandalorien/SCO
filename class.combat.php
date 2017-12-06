<?php
/**
 * Tis file is part of XNova:Legacies
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see http://www.xnova-ng.org/
 *
 * Copyright (c) 2009-Present, XNova Support Team <http://www.xnova-ng.org>
 * All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *                                --> NOTICE <--
 *  This file is part of the core development branch, changing its contents will
 * make you unable to use the automatic updates manager. Please refer to the
 * documentation for further information about customizing XNova.
 * 
 * use by mandalorien for Project - SCO
 */

class Combat{
	
	const TOURS = 6;
	private $_round;
	private $_ROUNDS = array();
	private $_ATTACKER = array();
	private $_ATTAQUANT = array();
	private $_TechnoAtt = array();
	private $_PowerAtt;
	private $_DEFENDER = array();
	private $_DEFENSEUR = array();
	private $_TechnoDef = array();
	private $_PowerDef;
	private $_Pricelist = array();
	private $_Combatcap = array();
	
	
	public function __construct($A,$B,$TA,$TB){
		global $pricelist,$CombatCaps;
		$this->_round = 1;
		$this->_ATTACKER = $A;
		$this->_DEFENDER = $B;
		$this->_ATTAQUANT = $A;
		$this->_DEFENSEUR = $B;
		$this->_TechnoAtt = $TA;
		$this->_TechnoDef = $TB;
		$this->_Pricelist = $pricelist;
		$this->_Combatcap = $CombatCaps;
		$this->_PowerAtt = $this->TotalPower($this->_ATTACKER,$this->_TechnoAtt);
		$this->_PowerDef = $this->TotalPower($this->_DEFENDER,$this->_TechnoDef);
	}
	
	function pretty_number($n, $floor = true) {
		if ($floor) {
			$n = floor($n);
		}
		return number_format($n, 0, ",", ".");
	}

	private function coque($type,$nombre){
		return (($this->_Pricelist[$type]['metal'] + $this->_Pricelist[$type]['crystal'])/10) * $nombre;
	}

	private function bouclier($type,$nombre){
		return $this->_Combatcap[$type]['shield'] * $nombre;
	}

	private function arme($type,$nombre){
		return $this->_Combatcap[$type]['attack'] * $nombre;
	}
	
	private function TotalAmount($array){
		$toto = 0;
		foreach($array AS $typeAtt=>$amountAtt){
			$toto += $amountAtt;
		}
		return $toto;
	}
	
	private function TotalPower($array,$techno){
		$toto = 0;
		foreach($array AS $typeAtt=>$amountAtt){
			$toto += $this->arme($typeAtt,$amountAtt) * (1 + (0.1 * ($techno["military_tech"])));
		}
		return $toto;
	}
	
	private function Traitement($typeAtt,$amountAtt,$typeDef,$amountDef,$TechnoAtt,$TechnoDef,$Rapidfire = false)
	{
		$ArmeAtt[$typeAtt] = $this->arme($typeAtt,$amountAtt) * (1 + (0.1 * ($TechnoAtt["military_tech"])));
		
		if(!$Rapidfire)
			$ShieldDef[$typeDef] = $this->bouclier($typeDef,$amountDef) * (1 + (0.1 * ($TechnoDef["shield_tech"])));
		else
			$ShieldDef[$typeDef] = 0;
		
		$CoqueDef[$typeDef]		= $this->coque($typeDef,$amountDef) * (1 + (0.1 * ($TechnoDef["defence_tech"])));
		
		var_dump("Les vaisseaux ".$typeAtt." (".$amountAtt.") Tire sur les vaisseaux " .$typeDef ." (".$amountDef.")");
		if($ArmeAtt[$typeAtt] > $ShieldDef[$typeDef]){
			
			var_dump("La puissance des vaisseaux ".$typeAtt." (".$this->pretty_number($ArmeAtt[$typeAtt]).") etaient superieur au bouclier (".$this->pretty_number($ShieldDef[$typeDef]).") des vaisseaux ".$typeDef." , les vaisseaux ".$typeDef." n'ont plus de bouclier");			
			$ArmeAtt[$typeAtt] -= $ShieldDef[$typeDef];
			var_dump("Il reste ".$this->pretty_number($ArmeAtt[$typeAtt])." de puissance au vaisseaux ".$typeAtt."");
			if($ArmeAtt[$typeAtt] >= $CoqueDef[$typeDef]){
				var_dump("la puissance des vaisseaux ".$typeAtt." (".$this->pretty_number($ArmeAtt[$typeAtt]).") est superieur a la coque (".$this->pretty_number($CoqueDef[$typeDef]).") des vaisseaux ".$typeDef.", les vaisseaux ".$typeDef." sont detruits");
				$CoqueDef [$typeDef]= 0;
				$ShieldDef[$typeDef] = 0;
				$amountDef = 0;
			}else{
				var_dump("la coque (".$this->pretty_number($CoqueDef[$typeDef]).") des vaisseaux ".$typeDef." a resister a la puissance des vaisseaux ".$typeAtt." (".$this->pretty_number($ArmeAtt[$typeAtt]).")");
				$coqueBefore[$typeDef] = $CoqueDef[$typeDef];
				$CoqueDef[$typeDef] -= $ArmeAtt[$typeAtt];
				var_dump("il reste (".$this->pretty_number($CoqueDef[$typeDef]).")de coque des vaisseaux ".$typeDef." sur ".$this->pretty_number($coqueBefore[$typeDef])."");
				$ShieldDef[$typeDef] = 0;
				$amountDef = round((($CoqueDef[$typeDef]/$coqueBefore[$typeDef])*$amountDef));
			}
		}else{
			$ShieldDef[$typeDef] -= $ArmeAtt[$typeAtt];
			var_dump("le bouclier des vaisseaux ".$typeDef." a absorbe ,il reste ".$ShieldDef[$typeDef]." de bouclier");
		}
		
		return $amountDef;
	}
	
	private function Traitement2($PowerAtt,$typeDef,$amountDef,$TechnoDef,$Rapidfire = false)
	{
		if(!$Rapidfire)
			$ShieldDef[$typeDef] = $this->bouclier($typeDef,$amountDef) * (1 + (0.1 * ($TechnoDef["shield_tech"])));
		else
			$ShieldDef[$typeDef] = 0;
		
		$CoqueDef[$typeDef]		= $this->coque($typeDef,$amountDef) * (1 + (0.1 * ($TechnoDef["defence_tech"])));
		
		var_dump("Les vaisseaux Agresseur  Tire sur les vaisseaux " .$typeDef ." (".$this->pretty_number($amountDef).")");
		if($PowerAtt > $ShieldDef[$typeDef]){
			
			var_dump("La puissance des vaisseaux Agresseur (".$this->pretty_number($PowerAtt).") etaient superieur au bouclier (".$this->pretty_number($ShieldDef[$typeDef]).") des vaisseaux ".$typeDef." , les vaisseaux ".$typeDef." n'ont plus de bouclier");			
			$PowerAtt -= $ShieldDef[$typeDef];
			var_dump("Il reste ".$this->pretty_number($PowerAtt)." de puissance au vaisseaux Agresseur");
			if($PowerAtt >= $CoqueDef[$typeDef]){
				var_dump("la puissance des vaisseaux Agresseur (".$this->pretty_number($PowerAtt).") est superieur a la coque (".$this->pretty_number($CoqueDef[$typeDef]).") des vaisseaux ".$typeDef.", les vaisseaux ".$typeDef." sont detruits");
				$CoqueDef [$typeDef]= 0;
				$ShieldDef[$typeDef] = 0;
				$amountDef = 0;
			}else{
				var_dump("la coque (".$this->pretty_number($CoqueDef[$typeDef]).") des vaisseaux ".$typeDef." a resister a la puissance des vaisseaux Agresseur (".$this->pretty_number($PowerAtt).")");
				$coqueBefore[$typeDef] = $CoqueDef[$typeDef];
				$CoqueDef[$typeDef] -= $PowerAtt;
				var_dump("il reste (".$this->pretty_number($CoqueDef[$typeDef]).")de coque des vaisseaux ".$typeDef." sur ".$this->pretty_number($coqueBefore[$typeDef])."");
				$ShieldDef[$typeDef] = 0;
				$amountDef = round((($CoqueDef[$typeDef]/$coqueBefore[$typeDef])*$amountDef));
			}
		}else{
			$ShieldDef[$typeDef] -= $PowerAtt;
			var_dump("le bouclier des vaisseaux ".$typeDef." a absorbe ,il reste ".$ShieldDef[$typeDef]." de bouclier");
		}
		
		return array($amountDef,$PowerAtt);
	}

	private function PhaseCombat(){
		
		var_dump("Phase de Combat Attaquant");
		foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeDef=>$amountDef){
			if($amountDef > 0 && $this->TotalAmount($this->_ATTACKER) > 0){
				if(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_DEFENSEUR[$typeDef])) * 100)) > 30){
					
					$resultat = $this->Traitement2($this->_PowerAtt,$typeDef,$amountDef,$this->_TechnoDef);
					$amountDef = $resultat[0];
					$this->_PowerAtt = $resultat[1];
					
				}else{
					if(abs(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_DEFENSEUR[$typeDef])) * 100)) - 100) >= rand(1,100)){
						$amountDef = 0;
					}		
				}
			}
			
			$this->_DEFENDER[$typeDef] = intval($amountDef);
		}
		
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
		
		var_dump("Phase de Combat Defenseur");
		foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeDef=>$amountDef){
			if($amountDef > 0 && $this->TotalAmount($this->_DEFENDER) > 0){
				
				if(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_ATTAQUANT[$typeDef])) * 100)) > 30){
					$resultat = $this->Traitement2($this->_PowerDef,$typeDef,$amountDef,$this->_TechnoAtt);
					$amountDef = $resultat[0];
					$this->_PowerDef = $resultat[1];

				}else{
					if(abs(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_ATTAQUANT[$typeDef])) * 100)) - 100) >= rand(1,100)){
						$amountDef = 0;
					}					
				}
			}
			
			$this->_ATTACKER[$typeDef] = intval($amountDef);
		}
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
	}
	
	private function PhaseAttaquant(){
		foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeAtt=>$amountAtt){
			foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeDef=>$amountDef){
				if($this->TotalAmount($this->_DEFENDER) > 0 && $amountAtt > 0){
					if(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_DEFENSEUR[$typeDef])) * 100)) > 30){
						
						$amountDef = $this->Traitement($typeAtt,$amountAtt,$typeDef,$amountDef,$this->_TechnoAtt,$this->_TechnoDef);
						
					}else{
						if(abs(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_DEFENSEUR[$typeDef])) * 100)) - 100) >= rand(1,100)){
							$amountDef = 0;
						}		
					}
				}
				
				$this->_ATTACKER[$typeAtt] = intval($amountAtt);
				$this->_DEFENDER[$typeDef] = intval($amountDef);
			}
		}
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
	}
	

	private function PhaseDefenseur(){
		foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeAtt=>$amountAtt){
			foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeDef=>$amountDef){
				if($this->TotalAmount($this->_ATTACKER) > 0 && $amountAtt > 0){
					
					if(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_ATTAQUANT[$typeDef])) * 100)) > 30){
						$amountDef = $this->Traitement($typeAtt,$amountAtt,$typeDef,$amountDef,$this->_TechnoDef,$this->_TechnoAtt);

					}else{
						if(abs(round(((self::COQUE($typeDef,$amountDef)/self::COQUE($typeDef,$this->_ATTAQUANT[$typeDef])) * 100)) - 100) >= rand(1,100)){
							$amountDef = 0;
						}					
					}
				}
				
				$this->_ATTACKER[$typeDef] = intval($amountDef);
				$this->_DEFENDER[$typeAtt] = intval($amountAtt);
			}
		}
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
	}
	
	private function PhaseRapidFire(){
		
		foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeAtt=>$amountAtt){
			foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeDef=>$amountDef){
				if($this->_Combatcap[$typeAtt]['sd'][$typeDef] > 1){
					var_dump("il y a un rapidfire du vaisseaux ".$typeAtt." sur le vaisseaux ".$typeDef."");
					if(rand(1,100) <= round((1-(1/$this->_Combatcap[$typeAtt]['sd'][$typeDef])) * 100)){
						if($this->TotalAmount($this->_DEFENDER) > 0){
								var_dump("les vaisseaux ".$typeAtt." retire ce tour ci sur les vaisseaux ".$typeDef."");
								$amountDef = $this->Traitement($typeAtt,$amountAtt,$typeDef,$amountDef,$this->_TechnoAtt,$this->_TechnoDef,true);
								$this->_ATTACKER[$typeAtt] = intval($amountAtt);
								$this->_DEFENDER[$typeDef] = intval($amountDef);
						}
					}
				}
			}
		}
		
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
		
		foreach($this->ROUNDS[$this->_round]['Defenseur'] AS $typeAtt=>$amountAtt){
			foreach($this->ROUNDS[$this->_round]['Attaquant'] AS $typeDef=>$amountDef){
				if($this->_Combatcap[$typeAtt]['sd'][$typeDef] > 1){
					var_dump("il y a un rapidfire du vaisseaux ".$typeAtt." sur le vaisseaux ".$typeDef."");
					if(rand(1,100) <= round((1-(1/$this->_Combatcap[$typeAtt]['sd'][$typeDef])) * 100)){
						if($this->TotalAmount($this->_ATTACKER) > 0){
								var_dump("les vaisseaux ".$typeAtt." retire ce tour ci sur les vaisseaux ".$typeDef."");
								$amountDef = $this->Traitement($typeAtt,$amountAtt,$typeDef,$amountDef,$this->_TechnoDef,$this->_TechnoAtt,true);
								$this->_ATTACKER[$typeDef] = intval($amountDef);
								$this->_DEFENDER[$typeAtt] = intval($amountAtt);
						}
					}
				}
			}
		}
		
		$this->ROUNDS[$this->_round] = array(
			'Attaquant'=>$this->_ATTACKER,
			'Defenseur'=>$this->_DEFENDER
		);
	}

	
	public function PhaseResultat(){
		for($this->_round = 1;$this->_round <= self::TOURS;$this->_round ++)
		{
			$this->ROUNDS[$this->_round] = array(
				'Attaquant'=>$this->_ATTACKER,
				'Defenseur'=>$this->_DEFENDER
			);

			
			if($this->TotalAmount($this->_ATTACKER) > 0 && $this->TotalAmount($this->_DEFENDER) > 0)
			{
				var_dump($this->ROUNDS[$this->_round]);
				var_dump("Tour " . $this->_round);
				// var_dump("Phase de Combat Attaquant");
				// $this->PhaseAttaquant();
				// var_dump("Phase de Combat Defenseur");
				// $this->PhaseDefenseur();
				$this->PhaseCombat();
				var_dump("Phase de RapidFire");
				$this->PhaseRapidFire();
				
				//fin du round on remet la puissance à fond !!! 
				$this->_PowerAtt = $this->TotalPower($this->_ATTACKER,$this->_TechnoAtt);
				$this->_PowerDef = $this->TotalPower($this->_DEFENDER,$this->_TechnoDef);
				
				var_dump($this->ROUNDS[$this->_round]);
				
			}else{
				var_dump("Fin du Combat au Tour ".$this->_round);
				break;
			}
		}
		return true;
	}
}
?>