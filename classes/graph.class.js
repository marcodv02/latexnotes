//import * as THREE from './../node_modules/three/build/three.module.js';
const THREE = require("three")

// Funzionanete const { OrbitControls } = require('three/examples/jsm/marco/OrbitControls.cjs');
const { OrbitControls } = require('./classes/OrbitControls.cjs');
//const { OrbitControls } = require('three/addons/controls/OrbitControls.js');

const sphereGeometry = new THREE.SphereGeometry(0.2, 32, 32);
const sphereMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff }); // Sfera bianca
const spheres= [];

class Node {
	// metodi della classe
	pos= null;
	ref_id= null;
	sphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
	latex_code= '';
	position_map= [//Inizio spigoli
	'UUM',
    'DDM',
    'UDM',
    'DUM',

    'UMD',
    'DMU',
    'UMU',
    'DMD',

    'MUU',
    'MDD',
    'MDU',
    'MUD', //Fine spigoli

      //angolo 19
      /*'UUU',
      'UUD',
      'UDU',
      'UDD',

      'DUU',
      'DUD',
      'DDU',
      'DDD',*/

    'UUU',
    'DUD',
    'UDU',
    'DDD',

    'DUU',
    'UUD',
    'DDU',
    'UDD',

    //Centri
    'MUM',
    'MDM',
    'MMU',
    'MMD',
    'UMM',
    'DMM'
  ];
  constructor(abs_ref, pos) {
    this.pos= pos;
    this.ref_id= abs_ref;
	
	this.sphere.position.set(pos.x, pos.y, pos.z);

    this.sphere.name = 'Ref:' + abs_ref;

    spheres.push(this.sphere);
    /*for (let i= 0; i<this.position_map.length; i++){
      for (let j= i+1; j<this.position_map.length; j++){
        if (this.position_map[i]===this.position_map[j]){
          console.log("DUE UGUALI", i, j);
          return;
        }
      }
    }*/
  }
  set_data(data) {
    //I should create a node
    let i= 0;
    data.forEach(el => {
      //console.log(this);
    });
  }
  get_pos_from_me(str) {
    let a= [0,0,0];
    for (let i= 0; i<3; i++) {
      switch (str[i]) {
        case 'U':
          a[i]= 1;
          break;
        case 'D':
          a[i]= -1;
          break;
      }
    }
    return new THREE.Vector3(a[1], a[0], a[2]).add(this.pos);
  }
	draw(scene) {
		scene.add(this.sphere);	
	}
  draw_all(scene) {
	let i= 0;
    this.position_map.forEach((el) => {
    const sphere = new THREE.Mesh(sphereGeometry, sphereMaterial);
      const v_pos= this.get_pos_from_me(el);

      sphere.position.set(v_pos.x, v_pos.y, v_pos.z);

      sphere.name = 'pippi' + el;

      spheres.push(sphere);
      setTimeout(function (){
        scene.add(sphere);
      }, 1000+i*500);
      i++;
    });
  }
};

class Graph {
	// Crea un cubo
	geometry = new THREE.BoxGeometry(2, 2, 2); // Puoi personalizzare le dimensioni del cubo come desideri

	// Crea un materiale trasparente
	material = new THREE.MeshBasicMaterial({
		color: 0x00ff00, // Puoi personalizzare il colore del cubo
		transparent: true, // Imposta il materiale come trasparente
		opacity: 0.5, // Imposta il livello di trasparenza (0.0 = completamente trasparente, 1.0 = opaco)
	});
	used_nodes= [];
	constructor(parentId) {
		if (!parentId) throw "Missing parameters";

		this.parent= document.getElementById(parentId);
		this.parent.innerHTML += `<div id="mod_graph"></div>`;
		const el= document.getElementById("mod_graph");
		let el_size= el.getBoundingClientRect();
		this.width= el_size.width;
		this.height= el_size.height;
		this.left= el_size.left;
		this.top= el_size.top;
		// Creazione della scena
		this.scene = new THREE.Scene();

		// Creazione della camera
		this.camera = new THREE.PerspectiveCamera(75, el_size.width / el_size.height, 0.1, 1000);
		this.camera.position.z= 5;
		this.camera.lookAt(0,0,0);
		
		this.raycaster = new THREE.Raycaster();
    	this.mouse = new THREE.Vector2();

    	// Registra l'evento per il movimento del mouse
    	el.addEventListener('mousemove', this.onMouseMove.bind(this), false);
		el.addEventListener('click', this.onClick.bind(this), false);
		// Creazione del renderer
		this.renderer = new THREE.WebGLRenderer({ alpha: true });
		this.renderer.setClearColor(0x000000, 0);
		this.renderer.setSize(el_size.width, el_size.height);
		el.appendChild(this.renderer.domElement);
		
		// Aggiungi i controlli OrbitControls

		const controls = new OrbitControls(this.camera, this.renderer.domElement);

		// Imposta le opzioni dei controlli (opzionale)
		controls.enableDamping = true; // Abilita l'ammortizzazione per una transizione più fluida
		controls.dampingFactor = 0.25; // Fattore di ammortizzazione (personalizzabile)
		controls.rotateSpeed = 0.35; // Velocità di rotazione (personalizzabile)
		
		const cube = new THREE.Mesh(this.geometry, this.material);

		// Imposta la posizione del cubo nell'origine
		cube.position.set(0, 0, 0);

		// Aggiungi il cubo alla scena
		this.scene.add(cube);
		
		this.updater = setInterval(() => {
            this.renderer.render(this.scene, this.camera);
        }, 100);

		this.test();
	}
	addNode(data)	{
		const Node_pos= this.calcPos(data, this.used_nodes);
		let node= new Node(0, new Node_pos);
		node.av_nodes(this.used_nodes)//Remove nodes position already taken from map
		this.used_nodes.append(node);
		//const me= new THREE.Vector3( camera.position.x, camera.position.y, camera.position.z);
	}
	test() {
		for (let i= 0; i<5; i++){
			const n= new Node(i, new THREE.Vector3(i, i, i));
			n.draw(this.scene);
			//console.log(this.used_nodes)
			this.used_nodes.push(n);
		}
		setTimeout(()=> {
			this.clear();
		}, 1000)
	}
	clear() {
		this.used_nodes.forEach(node => {
			this.scene.remove(node.sphere)
		});/*
		for (let node of this.used_nodes){
			console.log(node);
			this.scene.remove(node.sphere);	
		}*/
		console.log(this.used_nodes);
	}
	onClick(event) {
		console.log(event.clientX, this.width, this.mouse.x);
		// Aggiorna il raycaster
		this.raycaster.setFromCamera(this.mouse, this.camera);

		// Esegui l'intersezione con gli oggetti nella scena se necessario

		const intersects = this.raycaster.intersectObjects(spheres);

		if (intersects.length > 0) {
			// Una sfera è stata cliccata
			const clickedSphere = intersects[0].object;
			// Ora hai una referenza alla sfera cliccata (clickedSphere)
			console.log('Hai cliccato su:', clickedSphere.name);
  		}

	}
	onMouseMove(event) {
		// Calcola la posizione normalizzata del mouse rispetto alla finestra
		this.mouse.x = ((event.clientX - this.left) / this.width) * 2 - 1;//el.innerWidth) * 2 - 1;
		this.mouse.y = -((event.clientY - this.top) / this.height) * 2 + 1;//window.innerHeight) * 2 + 1;	
	}
}



module.exports = {
    Graph
};
