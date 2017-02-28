<script id="viewMapFS" type="x-shader/x-fragment">
	gl_FragColor = vec4(1., 0., 1., 1.);
</script>

<script id="viewMapVS" type="x-shader/x-vertex">
	attribute vec2 aVertexPosition;
	attribute vec2 aTextureCoord;
	attribute vec3 aVertexNormal;
	
	uniform mat4 uMVMatrix;
	
	gl_Position = uMVMatrix * vec4(aVertexPosition.x, 0.0, aVertexPosition.y);
</script>

/// Define all gl variables
var gl;
var mapVertexBuffer;
var mapIndexBuffer;

initGL = function(canvas) {
        try {
            gl = canvas.getContext("webgl");
            gl.viewportWidth = canvas.width;
            gl.viewportHeight = canvas.height;
			ANGLEia = gl.getExtension("ANGLE_instanced_arrays"); // Vendor prefixes may apply!
        } catch (e) {
        }
        if (!gl) {
            alert("Could not initialise WebGL, sorry :-(");
        }
    }
	
handleLoadedTexture = function(texture) {
	gl.bindTexture(gl.TEXTURE_2D, texture);
	gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false);
	gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, texture.image);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
	gl.bindTexture(gl.TEXTURE_2D, null);
	}

loadTexture = function(textureNumber, src) {
	requiredImages++;
	textureList[textureNumber].image = new Image();
	textureList[textureNumber].image.onload = function () {
		handleLoadedTexture(textureList[textureNumber]);
					loadedImages++;
					if (loadedImages == requiredImages) {initBuffers();}
		}
	textureList[textureNumber].image.src = src;
}
	
getShader = function(gl, id) {
	var shaderScript = document.getElementById(id);
	if (!shaderScript) {
		return null;
	}

	var str = "";
	var k = shaderScript.firstChild;
	while (k) {
		if (k.nodeType == 3) {
			str += k.textContent;
		}
		k = k.nextSibling;
	}

	var shader;
	if (shaderScript.type == "x-shader/x-fragment") {
		shader = gl.createShader(gl.FRAGMENT_SHADER);
	} else if (shaderScript.type == "x-shader/x-vertex") {
		shader = gl.createShader(gl.VERTEX_SHADER);
	} else {
		return null;
	}

	gl.shaderSource(shader, str);
	gl.compileShader(shader);

	if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
		alert(gl.getShaderInfoLog(shader));
		return null;
	}

	return shader;
    }
	
initShaders = function() {
	viewMapProgram = gl.createProgram();
	gl.attachShader(viewMapProgram, vertexShader);
	gl.attachShader(viewMapProgram, fragmentShader);
	gl.linkProgram(viewMapProgram);

	if (!gl.getProgramParameter(viewMapProgram, gl.LINK_STATUS)) {
		alert("Could not initialise shaders");
	}
	gl.useProgram(viewMapProgram);
	viewMapProgram.vertexPositionAttribute = gl.getAttribLocation(viewMapProgram, "aVertexPosition");
	gl.enableVertexAttribArray(viewMapProgram.vertexPositionAttribute);
}
		
setMatrixUniforms = function() {
	gl.uniformMatrix4fv(shaderProgram.pMatrixUniform, false, pMatrix);
	gl.uniformMatrix4fv(shaderProgram.mvMatrixUniform, false, mvMatrix);

	var normalMatrix = mat3.create();
	mat4.toInverseMat3(mvMatrix, normalMatrix);
	mat3.transpose(normalMatrix);
	gl.uniformMatrix3fv(shaderProgram.nMatrixUniform, false, normalMatrix);
}

setColorUniforms = function() {
	gl.uniformMatrix4fv(colorProgram.pMatrixUniform, false, pMatrix);
	gl.uniformMatrix4fv(colorProgram.mvMatrixUniform, false, mvMatrix);
}

setAreaUniforms = function () {
	gl.uniformMatrix4fv(areaProgram.pMatrixUniform, false, pMatrix);
	gl.uniformMatrix4fv(areaProgram.mvMatrixUniform, false, mvMatrix);
}
	
initBuffers = function() {
	mapVertexBuffer = new Array();
	
	for (var i=0; i<120; i++) {
		for (var j=0; j<120; j++) {
			mapVertexBuffer.push((j/60-1), (i/60-1));
		}
	}
	
	mapIndexBuffer = new Array();
	for (var i=0; i<119; i++) {
		mapIndexBuffer.push(i*120);
		for (var j=0; j<120; j++) {
			mapIndexBuffer.push(j+i*120, j+(i+1)*120);
		}
		mapIndexBuffer.push(119+(i+1)*120);
	}
	tick();
}

degToRad = function(degrees) {
	return degrees * Math.PI / 180;
}

drawScene = function() {
}
	
animate = function() {
	var timeNow = new Date().getTime();
	if (lastTime != 0) {
		var elapsed = timeNow - lastTime;
		rY += elapsed*wY;

		if (baseMap[0] < zoomLvl*120) {
			baseMap[0] = zoomLvl*120;
			xSpeed = 0;
			}
		else if (baseMap[0] > 14400-zoomLvl*120) {
			baseMap[0] = 14400-zoomLvl*120;
			xSpeed = 0;
			}

		if (baseMap[1] < zoomLvl*120) {
			baseMap[1] = zoomLvl*120;
			zSpeed = 0;
			}
		else if (baseMap[1] > 10800-zoomLvl*120) {
			baseMap[1] = 10800-zoomLvl*120;
			zSpeed = 0;
			}

		locTr[0] += 10*(-zSpeed*elapsed*Math.sin(rY)+xSpeed*elapsed*Math.cos(rY));
		locTr[1] += 10*(zSpeed*elapsed*Math.cos(rY)+xSpeed*elapsed*Math.sin(rY));
		if (locTr[0]<-10 && locTr[2]) {
			locTr[2] = 0;
			baseTile[0]--;
			switchOption = 0;
			initTiles(baseTile[0], baseTile[1], zoomLvl, [0,6,12,18,24,30], [5,11,17,23,29,35])
			}
		else if (locTr[0]>10 && locTr[2]) { //moving right
			locTr[2] = 0;
			baseTile[0]++;
			switchOption = 1;
			initTiles(baseTile[0], baseTile[1], zoomLvl, [5,11,17,23,29,35],  [0,6,12,18,24,30])
			}
		if (locTr[1]<-10 && locTr[2]) { // up
			locTr[2] = 0;
			baseTile[1]--;
			switchOption = 2;
			initTiles(baseTile[0], baseTile[1], zoomLvl, [0,1,2,3,4,5], [30,31,32,33,34,35])
			}
		else if (locTr[1]>10 && locTr[2]) { // down
			locTr[2] = 0;
			baseTile[1]++;
			switchOption = 3;
			initTiles(baseTile[0], baseTile[1], zoomLvl, [30,31,32,33,34,35], [0,1,2,3,4,5])
			}
		document.getElementById("baseTile").value = baseTile[0]+", "+baseTile[1];
		}
	cycleAdj = (timeNow/10000)%1.0;
	lastTime = timeNow;
	}

handleKeyDown = function(event) {
	currentlyPressedKeys[event.keyCode] = true;
	}

handleKeyUp = function(event) {
	currentlyPressedKeys[event.keyCode] = false;
	}
	
handleKeys = function() {
	if (currentlyPressedKeys[37] || currentlyPressedKeys[65]) {

		// Left cursor key or A
		xSpeed = -0.0005;
		} else if (currentlyPressedKeys[39] || currentlyPressedKeys[68]) {
		// Right cursor key or D
		xSpeed = 0.0005;
		} else {
		xSpeed = 0;
		}

	if (currentlyPressedKeys[38] || currentlyPressedKeys[87]) {

		// Up cursor key or W
		zSpeed = -0.0005;
		} else if (currentlyPressedKeys[40] || currentlyPressedKeys[83]) {
		// Down cursor key
		zSpeed = 0.0005;
		} else {
		zSpeed = 0;
		}
	if (currentlyPressedKeys[81]) {

		// Up cursor key or W
		wY = 0.001;
		} else if (currentlyPressedKeys[69]) {
		// Down cursor key
		wY = -0.001;
		} else {
		wY = 0;
		}
	}
	
tick = function() {
		requestAnimFrame(tick);
		handleKeys();
		drawScene();
		animate();
	}
	
MouseWheelHandler = function(e) {
	// cross-browser wheel delta
	var e = window.event || e; // old IE support
	delta = e.wheelDelta || -e.detail;
	delta = Math.max(Math.min(delta, 10.0), -10.0);
	mapScale = Math.max(Math.min(mapScale+delta/50.0,6.0),1.0);
	//mapScale += delta/50.0;
	viewAngle = degToRad(45);
	height = -10.0+Math.min(9.0, (zoomRot[zoomLvl]+mapScale-1)*1.5);

	dist = height/Math.tan(viewAngle);

	dCos = Math.cos(rY);
	dSin = Math.sin(rY);
	rotShift[0] = dist*dSin;
	rotShift[1] = dist*dCos;

	if (mapScale >= 2.0) {
		if (zoomLvl > 1 && locTr[4]) {
			locTr[4] = 0;

			baseTile[0] = Math.round(baseMap[0]/(120*zoomLvl/2))
			baseTile[1] = Math.round(baseMap[1]/(120*zoomLvl/2));

			document.getElementById("baseTile").value = baseTile[0]+", "+baseTile[1];
			switchOption = 4;
			getData("../public_html/rivers/loadRivers_v2.php", [zoomLvl/2, baseTile[0], baseTile[1], 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35], [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35]);
			//drawOrder = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
			initTiles(baseTile[0], baseTile[1], zoomLvl/2, [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35], [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35]);

			}
		if (mapScale >= 6.0 && zoomLvl == 1) mapScale = 6.0;
		else {

			}

		}
	else if (mapScale+delta/50.0 < 1.0) {
		if (zoomLvl < 8 && locTr[4]) {

			locTr[4] = 0;
			baseTile[0] = Math.round(baseMap[0]/(120*zoomLvl*2));
			baseTile[1] = Math.round(baseMap[1]/(120*zoomLvl*2));
			document.getElementById("baseTile").value = baseTile[0]+", "+baseTile[1];
			switchOption = 5;
			initTiles(baseTile[0], baseTile[1], zoomLvl*2, [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35], [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35]);
			//drawOrder = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35];
			}
		else {
			mapScale = 1.0;
			}
		}
	}
	
initTextureFramebuffer = function(trg, trgTex, width, height) {
	gl.bindFramebuffer(gl.FRAMEBUFFER, trg);

	gl.bindTexture(gl.TEXTURE_2D, trgTex);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR_MIPMAP_NEAREST);
	gl.generateMipmap(gl.TEXTURE_2D);

	gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, width, height, 0, gl.RGBA, gl.UNSIGNED_BYTE, null);

	trg.renderbuffer = gl.createRenderbuffer();
	gl.bindRenderbuffer(gl.RENDERBUFFER, trg.renderbuffer);
	gl.renderbufferStorage(gl.RENDERBUFFER, gl.DEPTH_COMPONENT16, width, height);

	gl.framebufferTexture2D(gl.FRAMEBUFFER, gl.COLOR_ATTACHMENT0, gl.TEXTURE_2D, trgTex, 0);
	gl.framebufferRenderbuffer(gl.FRAMEBUFFER, gl.DEPTH_ATTACHMENT, gl.RENDERBUFFER, trg.renderbuffer);

	gl.bindTexture(gl.TEXTURE_2D, null);
	gl.bindRenderbuffer(gl.RENDERBUFFER, null);

	gl.bindFramebuffer(gl.FRAMEBUFFER, null);
}

handleClick = function(event)	{
	document.body.style.cursor = "auto";
	var loc = findPos(this);
	var rect = this.getBoundingClientRect();
	var cpos = [(event.clientX - loc[0]), (document.getElementById("lesson03-canvas").height - (event.clientY - loc[1]))];

	var pixelValues = new Uint8Array(4);
	gl.bindFramebuffer(gl.FRAMEBUFFER, rttFramebuffer);
	gl.readPixels(cpos[0], cpos[1], 1, 1, gl.RGBA, gl.UNSIGNED_BYTE, pixelValues);
	gl.bindFramebuffer(gl.FRAMEBUFFER, null);
	if (pixelValues[0] > 36) {
		sendStr = "1019,"+pixelValues[0]+","+pixelValues[1]+","+pixelValues[2]+","+clickParams;
		//makeBox("unit", sendStr, 500, 500, 200, 50);
		passClick(sendStr, "rtPnl");
	}
	else {
		clickY = Math.floor(pixelValues[0]/6.0);
		clickX = pixelValues[0] - clickY*6;
		longitude = (baseTile[0] + clickX-3)*zoomLvl + pixelValues[1]*zoomLvl/255-30;
		latitude = 90 - ((baseTile[1]-3+clickY)*zoomLvl+zoomLvl*pixelValues[2]/255);document.getElementById("clickLat").value = latitude;
		document.getElementById("clickLong").value = longitude;
		sendStr = clickParams + ","+pixelValues+","+baseTile+","+zoomLvl;
		if (clickParams[0] != 0) {
			makeBox(clickTarg, sendStr, 500, 500, 200, 50);
		}
	}

	clickParams = [0];
	clickTarg = "";
}

canvasInit = function(new_canvas) {
	new_canvas.onclick = handleClick;

	new_canvas.style.width = 1200;
	new_canvas.style.height = 700;

	new_canvas.width = parseInt(new_canvas.style.width);
	new_canvas.height = parseInt(new_canvas.style.height);
}

createAndSetupTexture = function(gl) {
	var texture = gl.createTexture();
	gl.bindTexture(gl.TEXTURE_2D, texture);

	// Set up texture so we can render any size image and so we are
	// working with pixels.
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);

	return texture;
}

webGLStart = function() {
	document.getElementById("readMsg").addEventListener("click", function(event) {console.log(event);makeBox(\'inBox\', 1099, 500, 500, 200, 50)});

	setClick([0], "auto")
	var canvas = document.getElementById("gameCanvas");
	canvasInit(canvas);

	initGL(canvas);
	textureList[0] = gl.createTexture();
	loadTexture(0, "./textures/terrainTex3.png");


	initTiles(baseTile[0], baseTile[1], zoomLvl, [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35], [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35]);

	document.getElementById("baseOff").value = baseOffset[0]+", "+baseOffset[1];
	document.getElementById("baseTile").value = baseTile[0]+", "+baseTile[1];
	if (canvas.addEventListener) {
		// IE9, Chrome, Safari, Opera
		canvas.addEventListener("mousewheel", MouseWheelHandler, false);
		// Firefox
		canvas.addEventListener("DOMMouseScroll", MouseWheelHandler, false);
		}
	// IE 6/7/8
	else canvas.attachEvent("onmousewheel", MouseWheelHandler);
	
	gl.clearColor(0.0, 0.0, 0.0, 0.0);
	gl.enable(gl.DEPTH_TEST);

	document.onkeydown = handleKeyDown;
	document.onkeyup = handleKeyUp;

	initShaders();
}