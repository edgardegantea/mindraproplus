import random

def generate_empathetic_response(text: str, anxiety_prob: float) -> str:
    text_lower = text.lower()
    
    # 1. Categorías de palabras clave (Extremadamente amplias)
    saludos = ["hola", "buenos días", "buenas tardes", "buenas noches", "qué tal", "que tal", "hey", "qué onda", "buen dia", "quiubo", "holi", "holis", "saludos", "hi", "hello", "buenísima", "qué hay"]
    despedidas = ["adiós", "adios", "bye", "me voy", "hasta luego", "nos vemos", "gracias", "chao", "descansa", "hasta mañana", "me retiro", "buenas noches", "luego hablamos", "te dejo", "chaito"]
    
    academico = ["examen", "escuela", "universidad", "estudiar", "tesis", "calificación", "profesor", "profe", "clase", "reprobé", "maestro", "tarea", "materias", "semestre", "proyecto escolar", "exposición", "cuatrimestre", "reprobar", "estudio", "libros", "graduación"]
    laboral = ["trabajo", "jefe", "tarea", "proyecto", "oficina", "entrega", "entrevista", "compañero", "renunciar", "despido", "sueldo", "empresa", "cliente", "jornada", "estrés laboral", "negocio", "reunión", "junta", "compañeros", "horario", "salario"]
    tristeza = ["solo", "sola", "triste", "nadie", "llorar", "depresión", "vacio", "vacío", "mal", "desanimado", "llanto", "sin ganas", "aislado", "roto", "rota", "apagado", "infeliz", "melancolía", "lágrimas", "dolor en el alma", "no tengo ganas", "oscuridad", "apatía", "bajón", "deprimido", "deprimida", "hundido", "hundida", "sin rumbo"]
    ansiedad_aguda = ["miedo", "pánico", "taquicardia", "respirar", "ahogo", "corazón", "angustia", "desesperación", "susto", "tiemblo", "temblor", "ataque", "sudar", "sudor", "palpitaciones", "nervios", "ansiedad", "no puedo más", "me asfixio", "terror", "parálisis", "hiperventilando", "me falta el aire", "me voy a morir", "locura", "fobia"]
    sueno = ["dormir", "insomnio", "noche", "cansado", "cansada", "agotado", "agotada", "sueño", "desvelo", "madrugada", "pesadillas", "fatiga", "ojeras", "desperté", "no descanso", "sin energía", "no pego el ojo", "dormí mal", "cabeceando", "agotamiento", "pereza"]
    relacion_pareja = ["pareja", "novio", "novia", "esposo", "esposa", "ex", "matrimonio", "celos", "terminamos", "infiel", "relación", "rompimos", "divorcio", "prometido", "casarnos", "amante", "infidelidad", "engaño", "corazón roto", "amor"]
    relacion_familia = ["familia", "mamá", "papá", "hermano", "hermana", "hijo", "hija", "padres", "tíos", "abuelos", "casa", "hogar", "suegros", "primos", "familiares", "madre", "padre"]
    relacion_social = ["amigos", "amiga", "amigo", "pelea", "discusión", "ruptura", "traición", "mentira", "amistad", "conocido", "compañeros", "socializar", "gente", "me traicionó", "hipócrita", "falso", "falsa", "chusma", "grupo"]
    autoestima = ["feo", "fea", "tonto", "tonta", "inútil", "fracaso", "odio", "cuerpo", "gordo", "gorda", "no sirvo", "error", "horrible", "asco", "rechazo", "inseguridad", "culpa", "soy un asco", "soy un desastre", "me odio", "baja autoestima", "vergüenza", "nadie me quiere", "soy una carga", "estúpido", "estúpida", "torpe"]
    positivo = ["bien", "feliz", "logré", "conseguí", "mejor", "tranquilo", "excelente", "alegre", "buen día", "increíble", "genial", "super", "paz", "emocionado", "orgulloso", "éxito", "fantástico", "maravilloso", "contento", "motivada", "motivado", "esperanza", "triunfo", "aprobé", "gané"]
    frustracion = ["harto", "harta", "cansado de", "no soporto", "me choca", "estresado", "estresada", "ya no puedo", "presión", "explotar", "coraje", "enojo", "rabia", "molesto", "fastidio", "furia", "ira", "desesperante", "estoy al límite", "me caga", "odioso", "intolerable", "estoy harto"]
    salud_fisica = ["duele", "dolor", "cabeza", "estómago", "enfermo", "enferma", "médico", "doctor", "mareo", "náuseas", "pastillas", "medicamento", "hospital", "clínica", "gripe", "fiebre", "tensión", "músculos", "espalda", "cuello"]
    
    # 2. Identificar temas
    es_saludo = any(text_lower.startswith(word) or text_lower == word for word in saludos)
    es_despedida = any(word in text_lower for word in despedidas)
    
    tema_academico = any(word in text_lower for word in academico)
    tema_laboral = any(word in text_lower for word in laboral)
    tema_tristeza = any(word in text_lower for word in tristeza)
    tema_aguda = any(word in text_lower for word in ansiedad_aguda)
    tema_sueno = any(word in text_lower for word in sueno)
    tema_pareja = any(word in text_lower for word in relacion_pareja)
    tema_familia = any(word in text_lower for word in relacion_familia)
    tema_social = any(word in text_lower for word in relacion_social)
    tema_autoestima = any(word in text_lower for word in autoestima)
    tema_positivo = any(word in text_lower for word in positivo)
    tema_frustracion = any(word in text_lower for word in frustracion)
    tema_salud = any(word in text_lower for word in salud_fisica)
    
    # 3. Respuestas por Tema
    if es_saludo and len(text_lower.split()) < 4:
        return random.choice([
            "¡Hola! Qué gusto saludarte. ¿Cómo te sientes en este momento?",
            "Hola, aquí estoy para escucharte. ¿De qué tienes ganas de platicar hoy?",
            "¡Qué tal! Espero que tu día vaya de maravilla. ¿En qué te puedo ayudar?",
            "Hola. Tómate tu tiempo, estoy aquí para ti. ¿Qué tienes en mente?",
            "¡Hola, hola! Qué bueno verte por acá. ¿Cómo andamos de ánimos hoy?",
            "Hola. A veces solo necesitamos un espacio para soltar todo. Adelante, te leo.",
            "Buenas. Ya estoy aquí. Cuéntame, ¿cómo ha estado tu día?",
            "¡Hey! Cuéntame qué pasa por tu mente hoy.",
            "Hola. Un espacio seguro para hablar siempre viene bien. ¿De qué charlamos?",
            "¡Hola! ¿Cómo amaneciste hoy?",
            "Saludos. Me alegra que estés por aquí. ¿De qué quieres hablar?",
            "Hola, espero que todo esté marchando bien. Si no es así, aquí estoy para escucharte.",
            "¡Qué onda! Cuéntame, ¿qué tal te trata la vida hoy?",
            "Hola. Toma asiento virtual, ponte cómodo/a y dime qué piensas.",
            "¡Buen día! Estoy listo/a para leerte.",
            "Hola, hola. Soy todo oídos... bueno, todo ojos para leerte.",
            "¡Qué gusto! ¿Hay algo que te esté dando vueltas en la cabeza hoy?",
            "Hola. Aquí tienes un espacio sin juicios. ¿Cómo te encuentras?",
            "Buenas buenas. ¿Qué tal te ha tratado el universo hoy?",
            "Hola, espero que estés encontrando momentos de paz hoy. Cuéntame."
        ])
        
    if es_despedida and len(text_lower.split()) < 5:
        return random.choice([
            "Me alegra haber platicado contigo. Cuídate mucho y regresa cuando lo necesites.",
            "Hasta pronto. Recuerda que no estás solo/a, aquí estaré siempre que quieras charlar.",
            "Que tengas un excelente resto del día. ¡Toma agua y respira profundo!",
            "Gracias por abrirte conmigo hoy. ¡Te mando un abrazo enorme!",
            "¡Nos vemos! Trata de descansar y no ser tan duro/a contigo mismo/a.",
            "Chao, cuídate muchísimo. Recuerda que un mal día no define toda tu semana.",
            "Adiós por ahora. ¡Ojalá te vayas sintiendo un poquito más ligero/a!",
            "Desconéctate un rato, te hará bien. ¡Hasta luego!",
            "Me despido por ahora, pero estaré aquí apenas decidas volver. ¡Un abrazo!",
            "Hasta la próxima. Trata de buscar algo que te haga reír hoy, te lo mereces.",
            "Descansa mucho y no te sobreexijas. ¡Adiós!",
            "Nos vemos. Recuerda que eres más fuerte de lo que la ansiedad te hace creer.",
            "Espero que esta plática te haya servido. ¡Hasta la próxima!",
            "Que descanses muy bien, cierra los ojos y suelta la tensión. Bye bye.",
            "Un abrazo a la distancia. Vuelve cuando sientas que la mochila pesa mucho."
        ])

    if tema_aguda and anxiety_prob > 0.5:
        return random.choice([
            "Escucho que estás pasando por un momento de mucha angustia. Intenta respirar profundo conmigo: inhala en 4 segundos, sostén 4, exhala en 4. Estoy aquí.",
            "Sentir pánico es sumamente abrumador, pero recuerda que es una sensación temporal y va a pasar. ¿Puedes nombrar 3 cosas que ves a tu alrededor en este momento?",
            "Esos síntomas físicos asustan muchísimo, lo entiendo. Es la forma en que tu cuerpo reacciona a la tensión. Intenta sentarte, poner los pies firmes en el piso y respirar despacio.",
            "Sé que se siente como si perdieras el control, pero te prometo que estás a salvo. Vamos a concentrarnos solo en tu respiración. Cierra los ojos un momento si te sirve.",
            "Tranquilo/a, vamos un paso a la vez. Esa sensación en el pecho es muy fuerte, pero tu cuerpo sabe cómo calmarse. Toma un sorbo de agua fría si puedes.",
            "Te leo muy alterado/a y es súper válido. Cuando la ansiedad ataca fuerte, ayuda mucho tocar algo frío, como un hielo, o mojarte la cara. Estoy aquí.",
            "Los ataques de pánico mienten, te hacen creer que algo terrible va a pasar, pero estás a salvo. Concéntrate en el peso de tu cuerpo sobre la silla. Repítete: 'esto pasará'.",
            "Siento tu desesperación desde aquí. Vamos a anclarnos al presente: dime, ¿cuál es el objeto más cercano a tu mano derecha? Descríbemelo.",
            "El ahogo es una sensación horrible, lo sé. Intenta inhalar profundamente por la nariz inflando el estómago, y exhala como si soplaras una vela. Una vez más.",
            "Esa taquicardia es tu cuerpo encendiendo una falsa alarma. Todo está bien. No estás en peligro real. Respira profundo y dime qué hay a tu alrededor.",
            "Estoy aquí. No te voy a dejar solo/a con esto. Intenta caminar descalzo/a un momento por tu cuarto, siente el piso. A veces ayuda a bajar la energía al cuerpo.",
            "Sé que da mucho miedo, pero tú tienes el control, no la ansiedad. Respira profundo. Cuenta conmigo: 1, 2, 3, suelta el aire.",
            "Esta crisis va a terminar, te lo prometo. El cuerpo no puede mantener ese nivel de adrenalina para siempre. Solo agárrate fuerte y respira.",
            "Respira conmigo. Inhala despacio... Exhala despacio... Tu cerebro cree que hay un león enfrente, pero estás en un lugar seguro. Yo te acompaño.",
            "No pelees contra la sensación, solo déjala estar y enfócate en otra cosa física. ¿Puedes sentir la textura de tu ropa? Descríbela mentalmente.",
            "Es como una ola enorme, te revuelca pero al final siempre llegas a la orilla. Respira profundo y espera a que el mar se calme. Aquí estoy vigilando la orilla.",
            "La falta de aire asusta horrores, pero irónicamente estás hiperventilando por exceso de oxígeno. Trata de respirar dentro de tus manos o una bolsa de papel un minuto.",
            "Cierra los ojos. Escucha mi voz en tu cabeza: Estás bien. Estás a salvo. Esto es solo una tormenta química en tu cerebro y va a pasar. Respira."
        ])

    if tema_frustracion:
        return random.choice([
            "Es completamente comprensible que te sientas así de harto/a. A veces la cuerda se tensa demasiado. ¿Qué crees que fue la gota que derramó el vaso?",
            "Se lee mucha presión en tus palabras. Tienes derecho a estar enojado/a y frustrado/a. ¿Hay alguna forma en la que puedas soltar un poco de esa carga hoy?",
            "Uf, te entiendo perfecto. Llega un punto en el que uno dice 'ya no puedo más'. Está bien sentirse así. ¿Quieres desahogarte y soltar todo el coraje aquí?",
            "Vaya, suena a que la situación está súper pesada. Sentirse estresado/a al límite agota muchísimo. Tómate una pausa, aunque sea mental. Te leo.",
            "Toda esa frustración tiene que salir por algún lado. Es muy válido estar enojado con todo ahora mismo. ¿Qué sientes que aliviaría esa rabia de forma sana?",
            "Escucho tu hartazgo. Es de las sensaciones más pesadas. A veces solo queremos mandar todo a volar. ¡Grita o escribe todo tu coraje aquí, sin filtros!",
            "El coraje es mucha energía acumulada. No la reprimas, déjala fluir. Si necesitas maldecir, hazlo, te estoy leyendo.",
            "Cuando estamos hartos, todo se siente irritante. No te juzgues por perder la paciencia. ¿Hay algo de esto que realmente puedas cambiar ahora?",
            "Se vale estar cansado/a de intentarlo. Hoy puedes simplemente rendirte un ratito y enojarte. Mañana será otro día.",
            "Qué situación tan frustrante. Me enoja nada más de leerlo, te entiendo perfecto. Suéltalo todo, aquí te leo.",
            "A veces el estrés nos hace sentir que vamos a explotar como una olla exprés. Necesitas una válvula de escape urgente. ¿Qué te relaja normalmente?",
            "La rabia es agotadora. Literalmente quema la energía del cuerpo. Está bien decir basta y alejarte de la fuente del estrés si te es posible.",
            "Es injusto que tengas que cargar con tanta presión. Entiendo perfectamente por qué te sientes al límite. Te apoyo totalmente.",
            "A veces solo necesitamos romper un plato viejo o rayar muy fuerte una hoja de papel. Necesitas canalizar esa molestia. Te escucho.",
            "Si la frustración tuviera volumen, la tuya estaría al máximo. Quiero que sepas que te escucho y valido totalmente tu enojo."
        ])

    if tema_autoestima:
        return random.choice([
            "Es muy duro cuando nuestra propia mente nos dice cosas tan hirientes. Quiero recordarte que esos pensamientos son solo ruido, no definen tu valor real.",
            "Siento mucho que te juzgues con tanta severidad. A veces somos nuestros peores jueces. Piensa: ¿le dirías esas mismas palabras a alguien que quieres mucho?",
            "Eres muchísimo más que tus errores o la percepción que tienes de ti en un mal rato. Vamos a intentar ser un poquito más amables contigo. ¿Hay algo que te guste de ti?",
            "Me duele leer que te trates así. Todos cometemos errores y tenemos inseguridades, pero no mereces castigarte tanto. Aquí estoy para recordarte que vales mucho.",
            "Esos pensamientos intrusivos sobre uno mismo son de lo peor. Pero oye, eres humano/a, no tienes que ser perfecto/a. Trata de hablarte hoy como tu mejor amigo/a.",
            "La culpa y la inseguridad son lentes empañados; no te dejan ver tu verdadero reflejo. Eres suficiente tal cual eres. Respira y suelta el látigo un momento.",
            "El rechazo hacia uno mismo duele más que cualquier otra cosa. Pero recuerda que un error no borra todas tus virtudes. ¿Podrías nombrar algo de lo que estés orgulloso/a?",
            "Cuando nos sentimos fracasados, todo se vuelve oscuro. Pero la vida no es lineal. Estás en un bache, no en el final del camino. Sé compasivo/a contigo.",
            "Eres válido/a simplemente por existir, no necesitas probarle nada a nadie ni a tu propio espejo. Intenta abrazar tu vulnerabilidad hoy.",
            "Te leo y veo a alguien que se está exigiendo demasiado. Está bien no ser perfecto, está bien no saber qué hacer. Te prometo que vales muchísimo.",
            "Tu mente te está contando mentiras ahora mismo. Tú no eres tus defectos. Eres un montón de partes buenas que hoy simplemente están opacadas. Te abrazo.",
            "La autoestima es una planta que se riega despacito. Hoy no la arranques de raíz solo porque tiene un par de hojas secas. Eres valioso/a.",
            "Esa sensación de 'no ser suficiente' es una trampa. Eres más que suficiente. ¿Quién te enseñó a ser tan cruel contigo mismo/a? Es hora de desaprenderlo.",
            "Por favor, háblate con más cariño. Si pudieras verte a través de mis ojos o de alguien que te ama, nunca volverías a decirte cosas tan feas.",
            "Equivocarse es la única forma de aprender, no te hace un fracaso, te hace humano en entrenamiento. Yo apuesto por ti, levántate con calma."
        ])

    if tema_pareja:
        return random.choice([
            "Los temas de pareja pueden movernos el piso por completo. Todo se siente muy intenso. ¿Qué te hizo sentir exactamente lo que ocurrió?",
            "Entiendo que esta situación romántica te esté generando mucha angustia. A veces es difícil separar lo que sentimos de lo que debemos hacer.",
            "Las rupturas o crisis amorosas son de los dolores emocionales más fuertes. Date permiso de sentirlo sin juzgarte. Estoy aquí para acompañarte.",
            "Uff, lidiar con los celos o la desconfianza drena toda la energía. Recuerda priorizar tu paz mental por sobre todas las cosas. Te leo atentamente.",
            "Las discusiones de pareja son desgastantes porque tocan nuestras inseguridades más profundas. ¿Sientes que fuiste verdaderamente escuchado/a?",
            "A veces el amor no basta cuando la paz desaparece. Es súper válido que te duela tanto. Tómate el tiempo necesario para asimilar lo que pasó.",
            "Duele mucho cuando la persona que queremos nos decepciona o no nos entiende. No trates de arreglarlo todo hoy, solo siéntelo.",
            "El desamor o la confusión en la pareja es un proceso lento. No te presiones por 'estar bien' de inmediato. Aquí tienes un hombro virtual.",
            "La infidelidad o las mentiras quiebran la confianza en segundos, y reconstruirse toma meses. Tienes derecho a estar deshecho/a ahora mismo.",
            "Las relaciones a veces terminan su ciclo, y aunque es racional, el corazón no entiende de ciclos. Llora lo que necesites llorar.",
            "No te pierdas a ti mismo/a tratando de mantener a alguien más a tu lado. Tu relación más importante es contigo. Yo estoy aquí escuchándote.",
            "El silencio en la pareja a veces lastima más que los gritos. ¿Han podido sentarse a hablar honestamente de lo que sienten?"
        ])

    if tema_familia:
        return random.choice([
            "La familia sabe tocar botones emocionales que nadie más encuentra. ¿Sentiste que tus límites fueron cruzados en esta situación?",
            "Es complicado cuando las personas que deberían ser nuestro refugio nos causan estrés. Recuerda que tú no eres responsable de sus reacciones, solo de las tuyas.",
            "Tensión en casa es tensión constante. Está bien tomar distancia física o emocional de tu familia si eso protege tu paz. ¿Qué pasó exactamente?",
            "Las expectativas de los padres o familiares son mochilas muy pesadas que a veces cargamos sin darnos cuenta. ¿Crees que es justo llevar eso?",
            "Uf, las peleas familiares duelen porque hay mucha historia de por medio. No tienes que aguantar malos tratos solo porque 'son familia'. Te leo.",
            "Entiendo que te afecte tanto. A veces la familia nos decepciona y es un duelo muy particular. Cuéntame más, estoy de tu lado.",
            "Establecer límites con la familia es el paso más difícil, pero a veces es muy necesario. ¿Cómo te sientes respecto a tu propio espacio vital ahora mismo?",
            "A veces, nuestra familia biológica no es nuestro refugio seguro, y está bien buscar a nuestra familia elegida. No estás obligado a soportar toxicidad.",
            "El amor familiar no debe ser condicionado a que hagas lo que ellos quieren. Tú tienes tu propia voz y tu propio camino. Te leo atentamente.",
            "Sentirse incomprendido por las personas que te criaron duele profundamente. Pero eso no significa que haya algo mal contigo."
        ])

    if tema_social:
        return random.choice([
            "Los problemas con amigos pueden sentirse como una gran traición. ¿Sentiste que no te apoyaron como tú esperabas?",
            "A veces las amistades cambian y eso trae un duelo que nadie nos enseña a llevar. Es válido extrañar o sentir enojo. Cuéntame cómo se dio el conflicto.",
            "Perder a un amigo duele casi tanto como perder a una pareja. Es un dolor muy real. Tómate el tiempo para llorar esa pérdida si lo necesitas.",
            "Cuando la gente en quien confiamos nos falla, es natural querer aislarse. Pero recuerda que no todos son iguales. Estoy aquí para leerte.",
            "Las mentiras o rumores destruyen la confianza. Siento mucho que estés pasando por eso. ¿Tienes a alguien de confianza con quien puedas estar hoy?",
            "A veces es mejor estar solo que mal acompañado. Soltar amistades tóxicas es doloroso al principio, pero da mucha paz después. ¿Estás de acuerdo?",
            "Sentirse excluido/a de un grupo es una herida directa al ego y al corazón. Pero recuerda que tu valor no depende de pertenecer a ese círculo.",
            "La hipocresía desgasta muchísimo. Trata de rodearte solo de personas que te den paz y te sumen, no que te resten energía."
        ])

    if tema_tristeza:
        return random.choice([
            "Siento muchísimo que te sientas así. La tristeza a veces se siente como una cobija muy pesada. Quiero que sepas que no estás solo/a, yo te acompaño.",
            "Es completamente válido sentirse mal, no tener ganas de nada y solo querer llorar. Tómate el tiempo que necesites para procesarlo. Estoy aquí para ti.",
            "Me doy cuenta de que pasas por un momento muy gris. A veces, ponerlo en palabras ayuda a quitarle un poquito de peso. ¿Quieres contarme más?",
            "A veces el cuerpo simplemente nos pide parar y llorar para liberar la tensión acumulada. Permítete sentirlo. Cuando estés listo/a, cuéntame más.",
            "Esa sensación de vacío es muy difícil de llevar. Te abrazo a la distancia. No tienes que fingir que todo está bien aquí. ¿Has podido comer algo hoy?",
            "El desánimo es abrumador. No te obligues a 'estar bien' a la fuerza. Está bien estar mal. Estoy a un mensaje de distancia.",
            "Llorar limpia el alma, aunque suene a cliché. Deja salir esas lágrimas, tu cuerpo necesita esa liberación. Aquí estoy.",
            "Me rompe el corazón leerte tan triste. Recuerda que este sentimiento es como una nube gris, eventualmente se moverá. Yo te presto un paraguas.",
            "No tienes que ser fuerte hoy. Puedes simplemente acurrucarte y sentir tu tristeza. Es una emoción válida y necesita espacio.",
            "La melancolía que transmites se siente muy profunda. Te acompaño en este silencio, o si quieres platicar, aquí te leo con paciencia.",
            "Si la tristeza tuviera forma, siento que la tuya sería inmensa hoy. Pero no te ahogarás en ella, te prometo que el nivel del agua bajará. Respira.",
            "Es desgarrador leerte así. Quisiera tener una varita mágica para quitarte el dolor, pero como no la tengo, te ofrezco toda mi atención y comprensión.",
            "Hay días donde existir simplemente duele. Y está bien no querer hacer nada más que respirar. Sobrevivir a hoy es un logro inmenso.",
            "A veces el alma se nos rompe un poquito. Tómate el tiempo necesario para pegar las piezas. No hay prisa por sanar. Aquí estoy.",
            "Siento tu aislamiento. Si de algo sirve, detrás de esta pantalla hay alguien (o algo) que genuinamente desea que encuentres la paz pronto."
        ])

    if tema_academico:
        return random.choice([
            "El mundo académico puede ser cruel y sofocante. Por favor recuerda que tu valor como persona no se mide por una calificación ni por complacer a un maestro.",
            "Entiendo que los exámenes o tareas te generen este nivel de estrés. A veces sirve dividir todo en pasos minúsculos. ¿Cuál es el paso más chiquito ahora?",
            "Es súper normal sentir que el mundo se te viene encima cuando hay tanto por entregar. Intenta pausar 5 minutos, estirarte y tomar agua. Quejate todo lo que necesites aquí.",
            "Qué frustrante suena. El sistema educativo exige demasiado a veces. No te castigues si hoy no fuiste tu versión más productiva.",
            "Esa tesis o proyecto suena gigante, pero recuerda que se construye un párrafo a la vez. No veas toda la montaña, solo el siguiente escalón.",
            "Reprobar o sacar malas calificaciones asusta, pero no define tu futuro, te lo aseguro. Respira profundo, es solo un tropiezo en el camino.",
            "La exigencia de la escuela a veces parece no tener fin. Te aplaudo por seguir intentándolo, pero no olvides cuidar tu salud mental primero.",
            "Un número en una boleta jamás podrá medir tu inteligencia, tu creatividad o tu valor. Tómate un respiro, no te satures.",
            "Las desveladas por la escuela pasan una factura altísima a la mente. Te prometo que ninguna tarea vale más que tu descanso. Trata de cerrar los libros hoy.",
            "Los profesores a veces olvidan que tienes otras materias y una vida personal. Es totalmente injusto, tienes derecho a sentirte enojado/a y saturado/a."
        ])

    if tema_laboral:
        return random.choice([
            "El ambiente laboral puede consumirnos si no ponemos un alto. Recuerda que es solo trabajo, no es toda tu vida. ¿Qué es lo que más te agota de todo esto?",
            "Lidiar con jefes o compañeros difíciles es de las cosas que más roban la paz mental. ¿Has considerado poner límites más estrictos en tu horario?",
            "Qué pesado suena todo eso. El 'burnout' (síndrome del quemado) es real. No te exijas más de lo que tu cuerpo y mente pueden dar hoy. ¿Hay forma de delegar algo?",
            "El trabajo nunca se acaba, pero tu energía sí. Asegúrate de desconectar por completo al terminar tu jornada. Te leo, desahógate.",
            "Un despido o problema laboral ataca directo a nuestra sensación de seguridad. Es normal sentir mucha angustia. ¿Has podido ordenar tus opciones?",
            "La presión corporativa es brutal. No eres una máquina de productividad. Si necesitas renunciar mentalmente hoy para sobrevivir al día, hazlo.",
            "Qué tóxico suena ese ambiente. Nadie debería sacrificar su paz por un salario. Tómate un respiro y trata de desconectar el cerebro.",
            "Trabajar bajo amenaza constante o exceso de estrés destruye tu sistema nervioso. Necesitas un respiro profundo. ¿Cuándo es tu próximo descanso?",
            "A veces sentimos que el trabajo es el centro del universo, pero no lo es. Tú eres el centro. Cuídate primero. ¿Qué puedes dejar para mañana?",
            "Esa sobrecarga de tareas suena inhumana. Es imposible no sentir ansiedad cuando te exigen tanto. Tienes todo el derecho de sentirte abrumado/a."
        ])

    if tema_sueno:
        return random.choice([
            "El no poder descansar bien multiplica cualquier sentimiento de ansiedad o tristeza. Intenta soltar el celular un ratito. ¿Tu mente está dando muchas vueltas?",
            "El cansancio acumulado hace que hasta lo más pequeño parezca una montaña. Sé compasivo/a contigo mismo/a hoy. ¿Qué crees que no te está dejando dormir?",
            "Cuando estamos así de agotados, las emociones están a flor de piel. Hoy toca sobrevivir al día sin exigirse de más. ¿Has probado escuchar ruido blanco?",
            "Uf, el insomnio es una tortura. A veces ayuda salir de la cama unos minutos, leer algo aburrido o tomar un té cálido. Espero que puedas recuperar energía.",
            "Despertar y sentir que no dormiste nada es frustrante. No le pidas mucho a tu cerebro hoy, trabaja en 'modo ahorro de energía'. Te entiendo.",
            "Las pesadillas muchas veces son la ansiedad manifestándose de noche. Intenta escribir lo que soñaste y luego rómpelo, puede ayudar a liberar esa imagen.",
            "Esa fatiga mental se siente en los huesos. Si tienes la oportunidad, duerme una siesta, sin culpa. El cuerpo repara en el descanso.",
            "Dar vueltas en la cama solo genera más ansiedad. Acepta que ahora no puedes dormir, ponte cómodo/a y escucha música muy suave. Aquí estoy.",
            "El agotamiento nos hace ver los problemas al doble de su tamaño. Te prometo que todo se verá distinto después de una noche buena de sueño.",
            "Es como si el cerebro se encendiera justo cuando el cuerpo quiere apagarse. Qué molesto es eso. Trata de no frustrarte más por no dormir, solo respira."
        ])
        
    if tema_salud:
        return random.choice([
            "Los malestares físicos muchas veces son el grito del cuerpo cuando la mente está sobrecargada. Trata de escuchar a tu cuerpo. ¿Has podido descansar?",
            "Cualquier dolor o mareo asusta, especialmente con ansiedad. Recuerda que la tensión muscular puede causar mil síntomas extraños. Intenta relajarte.",
            "Cuando nos sentimos mal físicamente todo lo demás se vuelve 10 veces más pesado. No te esfuerces de más hoy. Cuídate mucho.",
            "Esa molestia física es real, no está solo en tu cabeza. Pero la ansiedad la puede amplificar. Trata de ponerte cómodo/a y enfócate en tu respiración.",
            "Es normal preocuparse por la salud, pero trata de no googlear tus síntomas si eso te da más pánico. Confía en tu médico y trata de distraer tu mente.",
            "Ojalá te sientas mejor muy pronto. Toma tus medicamentos y permítete estar en la cama viendo algo que te relaje. Yo te acompaño virtualmente.",
            "El dolor crónico o agudo es un ladrón de energía. No te frustres si hoy no pudiste hacer mucho. Tu prioridad número uno es sanar. Un abrazo.",
            "La tensión en el cuello o la cabeza es un clásico de la ansiedad. ¿Puedes intentar ponerte algo cálido en esa zona o darte un masaje suave?"
        ])

    if tema_positivo and not (tema_tristeza or tema_aguda or tema_academico or tema_frustracion or tema_laboral):
        return random.choice([
            "¡Qué gusto leer que las cosas van por buen camino! Momentos así recargan muchísimo la batería. ¿Qué fue lo que más disfrutaste de esto?",
            "Me alegra muchísimo sentir esa vibra positiva en tus palabras. Reconocer los buenos momentos y los pequeños logros es vital. ¡Sigue así!",
            "Esa tranquilidad que describes vale oro. Aprovéchala para conectar contigo mismo/a. ¿Tienes algún plan padre para el resto del día?",
            "¡Súper bien! Me da mucha felicidad saber que lograste eso que querías. Celébralo, te lo mereces en serio.",
            "Esa energía es contagiosa. Qué bonito que compartas tus victorias (grandes o pequeñas) por aquí.",
            "Atesora esta sensación de paz, guárdala en tu memoria para cuando vengan días grises. ¡Me da mucho gusto por ti!",
            "¡Qué excelente noticia! A veces somos tan duros con nosotros mismos que olvidamos celebrar lo bueno. ¡Un aplauso desde acá!",
            "Se lee mucha luz en tu mensaje. Que esta motivación te dure muchísimo y te impulse a seguir adelante.",
            "Sonreí al leer tu mensaje. Qué maravilla que las cosas estén saliendo como esperabas. ¡A disfrutar se ha dicho!",
            "Estar bien también es un logro. Respira esa paz profunda y disfrútala, te has ganado cada segundo de esa tranquilidad.",
            "¡Bravo! Cada pequeño paso positivo suma una enormidad. Celebra tu avance, yo desde aquí te echo porras.",
            "¡Me encanta leer esto! A veces la vida nos da respiros maravillosos. Cuéntame, ¿cómo vas a festejar este buen momento?"
        ])

    # 4. Respuestas genéricas basadas en la probabilidad de ansiedad
    if anxiety_prob > 0.7:
        return random.choice([
            "Te noto bastante tenso/a en lo que me compartes. Tómate un momento para respirar profundo, por favor. ¿Qué es lo que más te preocupa exactamente?",
            "Percibo mucha carga y pesadez en tus palabras. A veces soltarlo todo de golpe es el primer paso para aligerar la mochila. Estoy aquí para escucharte el tiempo que sea necesario.",
            "Entiendo perfecto que las cosas se sientan como un torbellino ahora mismo. Vamos un paso a la vez. ¿Qué crees que te daría un poco de paz en este momento?",
            "Suena a que hay mucho ruido en tu cabeza ahora mismo. Intenta enfocar tu atención en un solo objeto de tu cuarto por 10 segundos. Yo te espero, aquí sigo.",
            "Vaya, suena a una situación muy retadora. Recuerda que no tienes que resolverlo todo hoy. ¿Cómo te puedo apoyar ahorita?",
            "Me transmites mucha preocupación. A veces la ansiedad nos hace ver todo color negro. ¿Qué tal si nos concentramos en lo que sí puedes controlar ahora?",
            "Esa angustia se siente enorme, pero te prometo que no es más grande que tú. Trata de soltar los hombros y aflojar la mandíbula. ¿Quieres seguir platicando?",
            "Es súper válido sentirse abrumado/a con algo así. No trates de pelear con la sensación de nerviosismo, solo obsérvala y cuéntame más.",
            "Si tu mente va a mil por hora, vamos a intentar frenar un poquito. Tómate un vaso de agua fría. ¿Te sientes en condiciones de contarme más?",
            "Te abrazo fuerte. Sé que las cosas se sienten muy difíciles ahorita. Estoy prestándote toda mi atención. ¿Qué pasó después?",
            "Escucho muchísima presión en tus palabras. Detente un microsegundo: cierra los ojos, inhala profundo... exhala. Aquí estoy. No te preocupes por responder rápido.",
            "La ansiedad que refleja tu texto es palpable. Quiero que sepas que estoy aquí como una pared firme donde puedes rebotar todas tus preocupaciones sin miedo a caer.",
            "Siento que estás cargando con un muro muy pesado en la espalda. Suéltalo un momento aquí en el chat. ¿Qué es lo peor que te imaginas que puede pasar?",
            "Todo se ve caótico cuando la mente está alterada. No te exijas encontrar soluciones ahorita, solo date permiso de estar asustado/a o preocupado/a. Te escucho."
        ])
    elif anxiety_prob > 0.4:
        return random.choice([
            "Te leo con atención. Parece que hay varios temas rondando por tu cabeza que te generan inquietud. ¿Quieres que profundicemos en alguno de ellos?",
            "Comprendo lo que me cuentas. Es súper normal sentirse un poco fuera de eje a veces. ¿Cómo le has estado haciendo para lidiar con esto?",
            "Gracias por compartirme esto. Me da la impresión de que estás intentando procesar varias cosas a la vez. Estoy aquí por si necesitas usarme para ordenar tus ideas.",
            "Entiendo tu punto totalmente. A veces, el simple hecho de hablarlo (o escribirlo) ayuda a darle otra perspectiva. ¿Hay algo más que necesites sacar de tu sistema?",
            "Tiene mucho sentido lo que dices. ¿Y eso cómo te hace sentir físicamente? A veces el cuerpo guarda toda esa tensión.",
            "Sigo la idea de lo que me cuentas. Definitivamente no es una situación sencilla. ¿Tú cómo te sientes con todo esto al final del día?",
            "Me parece que estás cargando con varias dudas. ¿Has hablado de esto con alguien más?",
            "Comprendo tu punto de vista. Ojalá hubiera una solución mágica para esto, pero mientras la encuentras, yo te sigo escuchando.",
            "Es interesante cómo lo planteas. Se nota que es algo que te tiene pensando bastante. ¿Qué alternativas se te ocurren?",
            "Te voy siguiendo el hilo. A veces las cosas simplemente son confusas y no tienen una respuesta rápida. Cuéntame un poco más.",
            "Siento cierta inquietud en tus palabras. A veces las preocupaciones pequeñas se van acumulando. ¿Hay algo más que quieras contarme?",
            "Gracias por ser tan abierto/a. Me parece que estás reflexionando mucho sobre el tema. ¿Te sientes un poco mejor al ponerlo en palabras?",
            "Entiendo. Es el tipo de situación que deja a cualquiera pensativo o estresado. Si quieres, saca todas tus ideas aquí, no te interrumpo.",
            "Se lee como algo bastante molesto o incómodo. ¿Qué crees que sea lo mejor que podrías hacer por ti mismo/a hoy respecto a esto?",
            "La verdad es que no sueles enfrentar cosas así todos los días, ¿no? Es normal sentir esa confusión. Te sigo leyendo.",
            "Qué difícil posición. A veces uno quisiera apagar el cerebro un rato para dejar de analizar tanto todo, ¿te ha pasado?",
            "Me imagino que todo esto te ha estado robando energía. Asegúrate de descansar un poquito también. Sigue contándome si quieres.",
            "Percibo que esta situación te tiene con la mente muy ocupada. Tómalo con calma, a veces la claridad llega cuando uno menos se lo espera.",
            "Gracias por la confianza. Suena a que tienes muchas cosas en el plato ahora mismo. Vamos paso a paso, yo te acompaño.",
            "Esa incertidumbre es súper agotadora. A nadie le gusta no tener el control absoluto de lo que pasa. Te leo atentamente."
        ])
    else:
        return random.choice([
            "Qué interesante perspectiva. Gracias por compartirme cómo te sientes y lo que piensas. ¿Hay alguna otra cosa de la que te gustaría platicar hoy?",
            "Entiendo perfecto. Cuéntame más, ¿cómo te hace sentir todo eso en el fondo?",
            "Gracias por la confianza de abrirte conmigo. A veces no buscamos soluciones, solo queremos echar chisme o conversar. ¿Qué más tienes en mente?",
            "Me parece súper válido lo que comentas. Sigo el hilo de tus ideas, puedes continuar si quieres.",
            "Claro, tiene todo el sentido del mundo. ¿Y qué piensas hacer al respecto, o prefieres dejarlo ser por ahora?",
            "Ok, te voy leyendo. Me gusta cómo analizas las cosas. ¿Algo más que haya pasado en tu día?",
            "Es bueno reflexionar sobre estas cosas. Yo aquí estoy para escucharte sin prisas.",
            "Interesante lo que me compartes. ¿Siempre te has sentido así respecto a este tema?",
            "Te escucho (bueno, te leo). Adelante, sigue contándome, tengo todo el tiempo del mundo.",
            "Entiendo completamente. Esos detalles son los que marcan la diferencia. ¿Quieres agregar algo más a la historia?",
            "De acuerdo, tiene lógica. A veces la vida nos pone en situaciones curiosas. ¿Qué más te ronda por la mente?",
            "Me gusta platicar de estas cosas contigo. Siento que tienes una forma muy particular de ver el mundo.",
            "Anotado. Es súper válido lo que dices. ¿Y cómo pinta el resto de tu semana después de todo esto?",
            "Tiene muchísimo sentido. Gracias por tomarte el tiempo de contármelo con tanto detalle. ¿Algo más?",
            "Te voy leyendo. ¡Qué buena forma de plantearlo! Siéntete libre de contarme cualquier otra cosa que tengas guardada.",
            "Fíjate que nunca lo había visto desde ese ángulo, es un gran punto. ¿Qué otras cosas has estado pensando sobre esto?",
            "La vida tiene formas muy curiosas de presentarnos este tipo de situaciones. Sigue, sigue, me interesa saber más.",
            "¡Claro! Todos hemos pasado por algo similar en algún momento. Es súper liberador hablarlo. ¿Qué más pasó?",
            "Me encanta tu manera de contarlo. Aquí estoy, listo/a para seguir leyendo tus pensamientos al respecto.",
            "Vale, ya veo a qué te refieres. Y dime, ¿esto es algo que te pasa seguido o es completamente nuevo para ti?",
            "Eso suena como una anécdota bastante particular. Gracias por compartir este momento conmigo. ¿Cómo te sientes ahora?",
            "A veces solo necesitamos soltar las palabras y dejarlas en algún lado para que no pesen tanto en la cabeza. Te escucho.",
            "Eso que dices es súper interesante. Podríamos platicar de eso horas. ¿Cuál crees que sea el fondo de todo este asunto?",
            "Totalmente de acuerdo contigo. Hay cosas que uno simplemente tiene que procesar con calma. ¿Te ha ayudado hablarlo?",
            "Qué curiosa es la mente humana, ¿verdad? Siempre dándole vueltas a las cosas. Si quieres desentrañar más esa idea, adelante.",
            "Esa es una excelente forma de verlo. Me quedo con esa reflexión. ¿Qué otro tema traes en la mente hoy?",
            "Vaya, qué situación. Es de esas cosas que no sabes si reír o solo respirar profundo. Cuéntame el resto de la historia.",
            "Entiendo perfectamente tu punto. ¿Y hay alguien más en tu círculo con quien compartas estas mismas ideas?",
            "¡Qué buen tema de conversación! Siento que podríamos profundizar mucho en eso. Siéntete con la libertad de escribir todo lo que quieras.",
            "Ok, entiendo la historia completa. A veces uno solo necesita un espejo virtual para rebotar sus ideas, y para eso estoy aquí.",
            # SUGERENCIAS Y RECOMENDACIONES
            "Es súper interesante lo que mencionas. ¿Has intentado escribir todo eso en un papel? A veces ver las ideas fuera de la cabeza ayuda muchísimo.",
            "Te entiendo perfecto. Para despejar un poco la mente después de pensar en estas cosas, te sugiero dar una caminata corta de 10 minutos. Ayuda un montón.",
            "Ya veo. ¿Qué te parece si para equilibrar un poco las cosas hoy, intentas escuchar tu canción favorita o ver un capítulo de tu serie de confort?",
            "Me parece un gran punto. A veces darle tantas vueltas a algo nos estanca. Te sugiero hacer un cambio de actividad radical por 15 minutos: dibuja algo o prepárate un té.",
            "¡Totalmente! Y sabes, algo que sirve mucho en estos casos es hablarlo en voz alta frente a un espejo, o grabarte en un audio. Te sugiero intentarlo.",
            "Gracias por compartirlo. Oye, como sugerencia: hoy trata de regalarte un momento de autocuidado, aunque sea solo darte un baño largo. Te lo mereces.",
            "Sigo el hilo de lo que dices. Si te sientes un poco saturado/a con esto, mi mejor sugerencia es que desconectes el celular por una hora y leas un buen libro.",
            "Qué loco cómo funciona todo, ¿no? Si te sientes abrumado/a, te sugiero buscar un video de 'meditación guiada de 5 minutos' en YouTube. Hace maravillas.",
            "Comprendo completamente. Como pequeña sugerencia para distraerte: intenta aprender una habilidad inútil pero divertida hoy, como hacer malabares con limones.",
            "Me gusta cómo analizas la situación. ¿Te sugiero algo? Anota tres cosas buenas que hayan pasado hoy relacionadas o no con esto. Cambia mucho la perspectiva.",
            "Wow, sí. Y para no quedarte atrapado/a en ese pensamiento, te sugiero ponerte los audífonos y salir a caminar sin un rumbo fijo por un ratito.",
            "Entiendo tu posición. Te propongo un reto: haz algo creativo con tus manos hoy. Cocina algo nuevo, pinta, o arma un rompecabezas. Te ayudará a fluir mejor.",
            "Gracias por compartir. Te sugiero que, al terminar nuestra charla, te tomes un vaso de agua fresca y estires los brazos hacia el techo. A veces el cuerpo necesita reiniciar."
        ])
