var gl, mapBuffer, mapFrameBuffer, mapFrameTexture, squarePointsBuffer, squareIndexBuffer;
var elapsed, lastTime, timeNow;

var wY, rY, xVel, zVel;

var mvMatrix = mat4.create();
var pMatrix = mat4.create();
var mvMatrixStack = [];

//mapFrameBuffer = gl.createFramebuffer();
//initTextureFramebuffer(mapFrameBuffer, rttTexture, 1200, 700);

wY = 1;
rY = 0.0;
lastTime = new Date().getTime();
animate = function () {
	//console.log(wY);
	timeNow = new Date().getTime();
    elapsed = timeNow - lastTime;
	rY += (elapsed*wY);
	lastTime = timeNow;
	document.getElementById("rYVal").value = rY;
}

canvasInit = function () {
	canvas = document.getElementById("gameCanvas");

	canvas.onclick = handleClick;
	//canvas.addEventListener("click", function () {handleClick(event)});

	//new_canvas.style.width = 1200;
	//new_canvas.style.height = 700;

	//canvas.width = parseInt(canvas.style.width);
	//canvas.height = parseInt(canvas.style.height);

	canvas.width = 1200;
	canvas.height = 600;

	webGLStart(canvas);
	}

degToRad = function (degrees) {
		return degrees * Math.PI / 180;
}

drawScene = function () {
	gl.viewport(0, 0, gl.viewportWidth, gl.viewportHeight);
	gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

	mat4.perspective(45, gl.viewportWidth / gl.viewportHeight, 0.1, 100.0, pMatrix);


	mat4.identity(mvMatrix);

	
	mat4.translate(mvMatrix, [0., -0.5, -8.0]);
	mat4.rotate(mvMatrix, degToRad(rY), [0, 1, 0]);
	//mat4.rotate(mvMatrix, degToRad(-25), [1, 0, 0]);
	
	

	mvPushMatrix();
	gl.useProgram(bufferProgram);
	//mat4.rotate(mvMatrix, degToRad(45), [1, 0, 0]);
	
	
	// Draw the framebuffer
	gl.bindFramebuffer(gl.FRAMEBUFFER, mapFrameBuffer);
	gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
	
	gl.bindBuffer(gl.ARRAY_BUFFER, squarePointsBuffer);
	gl.vertexAttribPointer(bufferProgram.VPAttribute, 3, gl.FLOAT, false, 0, 0);

	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, squareIndexBuffer);
	setMatrixUniforms(bufferProgram);
	gl.drawElements(gl.TRIANGLE_STRIP, 4, gl.UNSIGNED_SHORT, 0);
	gl.bindFramebuffer(gl.FRAMEBUFFER, null);

	// draw the visible buffer
	gl.bindBuffer(gl.ARRAY_BUFFER, squarePointsBuffer);
	gl.vertexAttribPointer(bufferProgram.VPAttribute, 3, gl.FLOAT, false, 0, 0);

	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, squareIndexBuffer);
	setMatrixUniforms(bufferProgram);
	gl.drawElements(gl.TRIANGLE_STRIP, 4, gl.UNSIGNED_SHORT, 0);
	mvPopMatrix();
	//console.log(gl.getError());
}

findPos = function (obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
	}
	return [curleft,curtop];
}

getShader = function (gl, id) {
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

handleClick = function (event)	{
	console.log(event);
	console.log(this);
	document.body.style.cursor = "auto";
	var loc = findPos(this);
	var rect = this.getBoundingClientRect();
	var cpos = [(event.clientX - loc[0]), (document.getElementById("gameCanvas").height - (event.clientY - loc[1]))];

	var pixelValues = new Uint8Array(4);
	gl.bindFramebuffer(gl.FRAMEBUFFER, mapFrameBuffer);
	gl.readPixels(cpos[0], cpos[1], 1, 1, gl.RGBA, gl.UNSIGNED_BYTE, pixelValues);
	gl.bindFramebuffer(gl.FRAMEBUFFER, null);
	console.log(pixelValues);
	}
	
handleKeyDown = function (event) {
	currentlyPressedKeys[event.keyCode] = true;
	}

handleKeyUp = function (event) {
	currentlyPressedKeys[event.keyCode] = false;
	}

handleKeys = function () {
	//alert("set speed");
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
		wY = 0.1;
		} else if (currentlyPressedKeys[69]) {
		// Down cursor key
		wY = -0.1;
		} else {
		wY = 0.00;
		}
}

initBuffers = function () {
	squarePointsBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, squarePointsBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([ -1.0, -1.0,  0.0,
             1.0, -1.0,  0.0,
             -1.0,  1.0,  0.0,
            1.0,  1.0,  0.0,]), gl.STATIC_DRAW);

	squareIndexBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, squareIndexBuffer);
	gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array([0,1,2,3]), gl.STATIC_DRAW);
	
	mapFrameTexture = gl.createTexture();
	mapFrameBuffer = gl.createFramebuffer();
	initTextureFramebuffer(mapFrameBuffer, mapFrameTexture, 1200, 700);

	tick();
}

initTextureFramebuffer = function (trg, trgTex, width, height) {
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

initShaders = function () {
	var fragShader = getShader(gl, "buffer-fs");
	var vertShader = getShader(gl, "buffer-vs");

	console.log(fragShader);
	console.log(vertShader);

	bufferProgram = gl.createProgram();
	gl.attachShader(bufferProgram, vertShader);
	gl.attachShader(bufferProgram, fragShader);
	gl.linkProgram(bufferProgram);

	if (!gl.getProgramParameter(bufferProgram, gl.LINK_STATUS)) {
		alert("Could not initialise shaders - buffer");
	}
	gl.useProgram(bufferProgram);
	bufferProgram.VPAttribute = gl.getAttribLocation(bufferProgram, "aVertexPosition");
	gl.enableVertexAttribArray(bufferProgram.VPAttribute);

	//bufferProgram.textureCoordAttribute = gl.getAttribLocation(bufferProgram, "aTextureCoord");
	//gl.enableVertexAttribArray(bufferProgram.textureCoordAttribute);

	bufferProgram.pMatrixUniform = gl.getUniformLocation(bufferProgram, "uPMatrix");
  bufferProgram.mvMatrixUniform = gl.getUniformLocation(bufferProgram, "uMVMatrix");

	initBuffers();
}

initTextureFramebuffer = function (trg, trgTex, width, height) {
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

mvPushMatrix = function () {
      var copy = mat4.create();
      mat4.set(mvMatrix, copy);
      mvMatrixStack.push(copy);
  }

mvPopMatrix = function () {
      if (mvMatrixStack.length == 0) {
          throw "Invalid popMatrix!";
      }
      mvMatrix = mvMatrixStack.pop();
  }

setMatrixUniforms = function(shader) {
	gl.uniformMatrix4fv(shader.pMatrixUniform, false, pMatrix);
	gl.uniformMatrix4fv(shader.mvMatrixUniform, false, mvMatrix);

	/*
	var normalMatrix = mat3.create();
  mat4.toInverseMat3(mvMatrix, normalMatrix);
  mat3.transpose(normalMatrix);
  gl.uniformMatrix3fv(shader.nMatrixUniform, false, normalMatrix);*/
	}

tick = function () {
	requestAnimFrame(tick);
	handleKeys();
	drawScene();
	animate();
	}

webGLStart = function (canvas) {
	//canvas = document.getElementsByID("gameCanvas")
	console.log(canvas);
	try {
	    gl = canvas.getContext("experimental-webgl");
	    gl.viewportWidth = canvas.width;
	    gl.viewportHeight = canvas.height;
		ANGLEia = gl.getExtension("ANGLE_instanced_arrays"); // Vendor prefixes may apply!
    } catch (e) {
		console.log(e);
    }

	if (!gl) {
		alert("Could not initialise WebGL, sorry :-(");
    }

	gl.clearColor(0.0, 1.0, 0.0, 1.0);
    gl.enable(gl.DEPTH_TEST);
	
	document.onkeydown = handleKeyDown;
	document.onkeyup = handleKeyUp;
	
	initShaders();
    }
