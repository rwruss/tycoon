var gl, mapBuffer, mapFrameBuffer, squarePointsBuffer, squareIndexBuffer;
var elapsed, lastTime, timeNow;

var mvMatrix = mat4.create();
var pMatrix = mat4.create();

//mapFrameBuffer = gl.createFramebuffer();
//initTextureFramebuffer(mapFrameBuffer, rttTexture, 1200, 700);

animate = function () {
	timeNow = new Date().getTime();
    elapsed = timeNow - lastTime;
	rY += elapsed*wY;
	lastTime = timeNow;
}

canvasInit = function () {
	var new_canvas = document.getElementById("gameCanvas");

	//new_canvas.onclick = handleClick;
	new_canvas.addEventListener("onclick", handleClick(event));

	//new_canvas.style.width = 1200;
	//new_canvas.style.height = 700;

	new_canvas.width = parseInt(new_canvas.style.width);
	new_canvas.height = parseInt(new_canvas.style.height);
	}
	
drawScene = function () {
	
	gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
	gl.useProgram(bufferProgram);
	
	mat4.identity(mvMatrix);

	mat4.rotate(mvMatrix, degToRad(45), [1, 0, 0]);

	gl.useProgram(bufferProgram);
    bufferProgram.VPAttribute = gl.getAttribLocation(bufferProgram, "aVertexPosition");
    gl.enableVertexAttribArray(bufferProgram.VPAttribute);

	bufferProgram.textureCoordAttribute = gl.getAttribLocation(bufferProgram, "aTextureCoord");
    gl.enableVertexAttribArray(bufferProgram.textureCoordAttribute);

    bufferProgram.pMatrixUniform = gl.getUniformLocation(bufferProgram, "uPMatrix");
    bufferProgram.mvMatrixUniform = gl.getUniformLocation(bufferProgram, "uMVMatrix");

	bufferProgram.samplerUniform = gl.getUniformLocation(bufferProgram, "uSampler");
	bufferProgram.tileNumberUniform = gl.getUniformLocation(bufferProgram, "uTileNum");
	bufferProgram.scaleUniform = gl.getUniformLocation(bufferProgram, "uMapScale");
	bufferProgram.offsetUniform = gl.getUniformLocation(bufferProgram, "uMapOffset");
	
	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, indexBuffer);
	gl.drawElements(gl.TRIANGLE_STRIP, drawLength, gl.UNSIGNED_SHORT, 0);
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

handleClick = function (event)	{
	document.body.style.cursor = "auto";
	var loc = findPos(this);
	var rect = this.getBoundingClientRect();
	var cpos = [(event.clientX - loc[0]), (document.getElementById("gameCanvas").height - (event.clientY - loc[1]))];

	var pixelValues = new Uint8Array(4);
	gl.bindFramebuffer(gl.FRAMEBUFFER, mapFrameBuffer);
	gl.readPixels(cpos[0], cpos[1], 1, 1, gl.RGBA, gl.UNSIGNED_BYTE, pixelValues);
	gl.bindFramebuffer(gl.FRAMEBUFFER, null);
	}
	
initBuffers = function () {
	tick();
	
	squarePointsBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, squarePointsBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1., 0., -1., -1., 0., 1., 1., 0., -1., 1., 0., 1.]), gl.STATIC_DRAW);
	
	squareIndexBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, squareIndexBuffer);
	gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array([0,1,2,3]), gl.STATIC_DRAW);
}

initShaders = function () {
	var fragShader = getShader(gl, "buffer-fs");
	var vertShader = getShader(gl, "buffer-vs");
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

	bufferProgram.textureCoordAttribute = gl.getAttribLocation(bufferProgram, "aTextureCoord");
	gl.enableVertexAttribArray(bufferProgram.textureCoordAttribute);

	bufferProgram.pMatrixUniform = gl.getUniformLocation(bufferProgram, "uPMatrix");
	bufferProgram.mvMatrixUniform = gl.getUniformLocation(bufferProgram, "uMVMatrix");
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
	
setMatrixUniforms = function(shader) {
        gl.uniformMatrix4fv(shader.pMatrixUniform, false, pMatrix);
        gl.uniformMatrix4fv(shader.mvMatrixUniform, false, mvMatrix);

		var normalMatrix = mat3.create();
        mat4.toInverseMat3(mvMatrix, normalMatrix);
        mat3.transpose(normalMatrix);
        gl.uniformMatrix3fv(shader.nMatrixUniform, false, normalMatrix);
		}
	
tick = function () {
	requestAnimFrame(tick);
	handleKeys();
	drawScene();
	animate();
	}

webGLStart = function (canvas) {
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
		
	gl.clearColor(1.0, 0.0, 0.0, 0.0);
    gl.enable(gl.DEPTH_TEST);
	initShaders();
    }
