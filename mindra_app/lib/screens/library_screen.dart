import 'package:flutter/material.dart';
import '../theme/mindra_theme.dart';
import '../utils/responsive.dart';

// ─── Modelos ──────────────────────────────────────────────────────────────────
class _Article {
  final String id;
  final String category;
  final String emoji;
  final String title;
  final String summary;
  final String body;
  final Color color;
  const _Article({
    required this.id,
    required this.category,
    required this.emoji,
    required this.title,
    required this.summary,
    required this.body,
    required this.color,
  });
}

// ─── Contenido de la biblioteca ───────────────────────────────────────────────
const List<_Article> _kArticles = [
  // ── Qué es la ansiedad
  _Article(
    id: 'a1',
    category: 'Fundamentos',
    emoji: '🧠',
    title: '¿Qué es la ansiedad?',
    color: Color(0xFF4f46e5),
    summary:
        'Entender la ansiedad como respuesta adaptativa y cuándo se convierte en problema.',
    body: '''
La ansiedad es una respuesta natural del organismo ante situaciones percibidas como amenazantes. Evolutivamente, nos preparó para huir o luchar frente al peligro.

**Síntomas comunes**
• Palpitaciones o taquicardia
• Respiración acelerada o superficial
• Tensión muscular
• Pensamientos intrusivos o catastróficos
• Evitación de situaciones

**¿Cuándo es un problema?**
La ansiedad se convierte en trastorno cuando:
1. Es desproporcionada al estímulo
2. Dura más de 6 meses (ansiedad generalizada)
3. Interfiere con la vida cotidiana

**Tipos principales**
• **TAG** (Trastorno de Ansiedad Generalizada): preocupación excesiva por muchas áreas
• **Trastorno de pánico**: ataques intensos repentinos
• **Fobia social**: miedo a situaciones sociales
• **TEPT**: tras eventos traumáticos

La buena noticia: la ansiedad tiene tratamiento efectivo con TCC, mindfulness y, cuando es necesario, apoyo farmacológico.
''',
  ),

  // ── Pensamiento catastrófico
  _Article(
    id: 'a2',
    category: 'TCC',
    emoji: '💭',
    title: 'Pensamiento catastrófico',
    color: Color(0xFF7c3aed),
    summary:
        'Cómo identificar y desafiar los pensamientos "todo o nada" que amplifican la ansiedad.',
    body: '''
El pensamiento catastrófico es una distorsión cognitiva en la que anticipamos el peor escenario posible como si fuera inevitable.

**Ejemplos típicos**
• "Si me equivoco en la presentación, me van a despedir"
• "Siento que me late el corazón raro, seguro es un infarto"
• "Lleva 10 minutos tarde, algo malo le pasó"

**El ciclo del catastrofismo**
Situación → Interpretación catastrófica → Ansiedad/pánico → Evitación → Confirmación del peligro percibido

**Técnica de las 3 preguntas (TCC)**
Ante un pensamiento catastrófico, pregúntate:
1. ¿Cuál es la evidencia A FAVOR de este pensamiento?
2. ¿Cuál es la evidencia EN CONTRA?
3. ¿Cuál sería una interpretación más equilibrada?

**Práctica diaria**
Lleva un registro de tus pensamientos automáticos en el diario de Mindra. Con el tiempo, entrenarás a tu mente a responder de forma más flexible.
''',
  ),

  // ── Respiración
  _Article(
    id: 'a3',
    category: 'Técnicas',
    emoji: '🌬️',
    title: 'Por qué funciona la respiración',
    color: Color(0xFF0891b2),
    summary:
        'La ciencia detrás del control respiratorio como regulador del sistema nervioso.',
    body: '''
Cuando estamos ansiosos, el sistema nervioso simpático ("modo lucha-huida") se activa. La respiración consciente activa el sistema parasimpático ("modo calma"), contrarrestando la respuesta de estrés.

**El nervio vago**
La respiración profunda y lenta estimula el nervio vago, que baja la frecuencia cardíaca, reduce la presión arterial y señala seguridad al cerebro.

**¿Por qué la exhalación larga?**
La exhalación activa más el parasimpático que la inhalación. Por eso técnicas como 4-7-8 o la respiración coherente (5-5) tienen el énfasis en el aire saliente.

**Técnicas basadas en evidencia**
| Técnica | Ritmo | Efecto |
|---------|-------|--------|
| Respiración diafragmática | 4-4 | Calma general |
| 4-7-8 | Inhala 4, sostén 7, exhala 8 | Anti-ansiedad |
| Caja (box breathing) | 4-4-4-4 | Enfoque y calma |
| Coherente | 5-5 | Variabilidad cardíaca |

Practica con la sección "Técnicas" de Mindra — incluye animaciones guiadas para cada ejercicio.
''',
  ),

  // ── Mindfulness
  _Article(
    id: 'a4',
    category: 'Fundamentos',
    emoji: '🧘',
    title: 'Mindfulness: prestar atención sin juzgar',
    color: Color(0xFF16a34a),
    summary:
        'Qué es el mindfulness, cómo difiere de la meditación y cómo empezar en 5 minutos.',
    body: '''
Mindfulness (atención plena) es la capacidad de prestar atención al momento presente, de manera intencional y sin juicio.

**¿Es lo mismo que meditar?**
La meditación es una práctica formal; el mindfulness es una actitud que se puede aplicar en cualquier momento: al comer, caminar, ducharse o escuchar.

**Beneficios respaldados por ciencia**
• Reducción del cortisol (hormona del estrés) – 8 semanas de práctica
• Mayor regulación emocional
• Reducción de la rumia (pensar en bucle)
• Mejora del sueño
• Menor activación de la amígdala (centro del miedo)

**Ejercicio de 5 minutos: STOP**
1. **S** — Para (Stop)
2. **T** — Respira (Take a breath)
3. **O** — Observa qué sientes sin cambiarlo
4. **P** — Continúa (Proceed)

**Señales de que estás practicando bien**
No hay práctica "perfecta". Si notas que tu mente se fue y la trajiste de vuelta, eso ES mindfulness. Cada retorno cuenta.
''',
  ),

  // ── Sueño
  _Article(
    id: 'a5',
    category: 'Bienestar',
    emoji: '🌙',
    title: 'Ansiedad y sueño: el círculo vicioso',
    color: Color(0xFF7c3aed),
    summary:
        'Por qué la ansiedad arruina el sueño y qué hacer para romper el ciclo.',
    body: '''
La ansiedad y el mal sueño se alimentan mutuamente: la ansiedad dificulta dormir, y la falta de sueño eleva la ansiedad al día siguiente.

**¿Qué pasa en el cerebro?**
La amígdala (procesadora del miedo) se vuelve 60% más reactiva tras una noche sin dormir bien. El córtex prefrontal (racionalidad) pierde capacidad de inhibirla.

**Errores comunes**
• Mirar el reloj repetidamente (aumenta la presión por dormir)
• Quedarse en cama despierto más de 20 min (condicionamiento negativo)
• Pantallas azules (suprimen melatonina hasta 3h)
• Alcohol (fragmenta el sueño REM)

**Higiene del sueño: lo que sí funciona**
1. Hora fija de levantarse (incluso fines de semana)
2. Solo la cama para dormir y sexo — no pantallas, no trabajo
3. Temperatura del cuarto: 18–20°C
4. Rutina de 30 min antes: baño tibio, lectura ligera, respiración
5. Exposición a luz natural en los primeros 30 min del día

**Técnica: "tiempo de preocupaciones"**
Reserva 15 min en la tarde para escribir tus preocupaciones. Cuando aparezcan de noche, dite: "Ya lo agendé para mañana".
''',
  ),

  // ── Autocuidado
  _Article(
    id: 'a6',
    category: 'Bienestar',
    emoji: '💚',
    title: 'Autocuidado real (no el de Instagram)',
    color: Color(0xFF059669),
    summary:
        'Qué es el autocuidado basado en evidencia y cómo diferenciarlo del consumismo de bienestar.',
    body: '''
El autocuidado genuino no siempre se siente bien en el momento — a veces es hacer lo difícil que te cuida a largo plazo.

**Los 4 pilares reales**

**1. Sueño** (el más importante)
7–9 horas para adultos. No negociable para regular emociones y memoria.

**2. Movimiento**
150 min/semana de actividad moderada. El ejercicio reduce la ansiedad con eficacia comparable a los ansiolíticos en casos leves-moderados.

**3. Conexión social**
El aislamiento es tan dañino como fumar 15 cigarrillos/día (Holt-Lunstad, 2015). Prioriza conversaciones reales sobre redes sociales.

**4. Sentido y propósito**
Hacer cosas con sentido (no solo placer) activa el sistema de recompensa de forma más duradera.

**Lo que NO es autocuidado con base en evidencia**
× Baños de sales sin el resto
× Comprar ropa para "sentirte mejor"
× Scrolling infinito como "descanso"

**Una práctica de 2 minutos: agradecimiento**
Cada noche, escribe 3 cosas específicas por las que estás agradecido/a. La especificidad importa: "el café de esta mañana" > "mi familia".
''',
  ),

  // ── Regulación emocional
  _Article(
    id: 'a7',
    category: 'TCC',
    emoji: '🌊',
    title: 'Regulación emocional: surf en lugar de lucha',
    color: Color(0xFF0891b2),
    summary:
        'Por qué resistir las emociones las amplifica y cómo aprender a surfearlas.',
    body: '''
Las emociones son como olas: tienen una curva natural de subida, pico y bajada. Si las resistimos, las mantenemos en el pico más tiempo.

**El paradox de la supresión**
Trata de NO pensar en un oso blanco ahora mismo.

¿Pudiste? El experimento de Wegner (1987) demostró que intentar suprimir un pensamiento lo hace más frecuente — el "efecto rebote".

**La técnica TIPP (DBT)**
Cuando la emoción es muy intensa:
1. **T** — Temperature: agua fría en la cara (activa el reflejo de buceo, reduce el ritmo cardíaco)
2. **I** — Intense exercise: 20 min de movimiento vigoroso
3. **P** — Paced breathing: respiración lenta, exhalar más que inhalar
4. **P** — Progressive relaxation: relajación muscular progresiva

**Defusión cognitiva**
En lugar de pensar "Estoy ansioso", prueba: "Noto que mi mente está teniendo el pensamiento de que estoy ansioso". Esa distancia cambia la relación con la emoción.

**Ventana de tolerancia**
Hay un rango óptimo entre la hiperactivación (ansiedad/pánico) y la hipoactivación (disociación/entumecimiento). Las técnicas de Mindra te ayudan a mantenerte en esa ventana.
''',
  ),

  // ── Terapia
  _Article(
    id: 'a8',
    category: 'Recursos',
    emoji: '🤝',
    title: '¿Cuándo buscar ayuda profesional?',
    color: Color(0xFFdc2626),
    summary:
        'Señales claras de que es momento de consultar a un psicólogo o psiquiatra.',
    body: '''
Mindra es una herramienta de apoyo y autoconocimiento, no un reemplazo de la atención clínica. Hay momentos en que la ayuda profesional es esencial.

**Señales de alerta**
• La ansiedad interfiere con tu trabajo, relaciones o vida cotidiana durante más de 2 semanas
• Pensamientos de hacerte daño o de que sería mejor no estar
• Ataques de pánico frecuentes o inexplicables
• Consumo de alcohol o sustancias para manejar la ansiedad
• Aislamiento progresivo

**Si hay pensamientos de autolesión o suicidio, busca ayuda hoy:**
• SAPTEL: 55 5259-8121 (24/7, gratuito)
• IMSS salud mental: 800 890-2000
• Emergencias: 911

**Tipos de profesionales**
| Profesional | ¿Qué hace? |
|-------------|-----------|
| Psicólogo/a | Psicoterapia (TCC, DBT, EMDR) |
| Psiquiatra  | Diagnóstico + medicación si es necesario |
| Psicoterapeuta | Psicoterapia con formación diversa |

**Cómo encontrar ayuda en México**
• IMSS/ISSSTE: solicita referencia a salud mental
• UNAM/UAM: clínicas universitarias de bajo costo
• Comunidad de práctica de psicólogos: colegiopsicologos.mx

Buscar ayuda es un acto de valentía, no de debilidad.
''',
  ),

  // ── Estrés laboral
  _Article(
    id: 'a9',
    category: 'Bienestar',
    emoji: '💼',
    title: 'Estrés laboral y límites saludables',
    color: Color(0xFFca8a04),
    summary:
        'Diferencia entre estrés productivo y burnout, y cómo poner límites sin culpa.',
    body: '''
El estrés laboral moderado puede mejorar el rendimiento (estrés eustréss). El problema es cuando se vuelve crónico y no hay recuperación.

**Burnout: las 3 dimensiones**
1. **Agotamiento emocional**: vacío, no tienes más para dar
2. **Despersonalización**: cinismo, desapego de tus tareas o colegas
3. **Reducción del logro**: sensación de que nada de lo que haces importa

**Señales tempranas**
• Dificultad para desconectarte fuera del horario
• Irritabilidad con familia o amigos por cosas del trabajo
• Dificultad para concentrarte
• Enfermedades frecuentes (sistema inmune deprimido por cortisol crónico)

**Límites saludables: el kit básico**
1. Notificaciones laborales apagadas después de X hora
2. "No" como respuesta completa (no requiere justificación larga)
3. Tiempo de buffer entre reuniones (5-10 min)
4. Vacaciones reales (sin revisar correo)

**La paradoja del rendimiento**
Trabajar más horas no produce más. Después de 55 h/semana el rendimiento equivale a trabajar 70 h (Stanford). El descanso no es opcional — es parte del trabajo.
''',
  ),

  // ── Comunicación asertiva
  _Article(
    id: 'a10',
    category: 'Habilidades',
    emoji: '🗣️',
    title: 'Comunicación asertiva',
    color: Color(0xFF4f46e5),
    summary:
        'Cómo expresar lo que necesitas sin agredir ni someterte — el punto medio entre pasividad y agresividad.',
    body: '''
La comunicación asertiva es la habilidad de expresar pensamientos, sentimientos y necesidades de forma directa, honesta y respetuosa.

**Los 3 estilos de comunicación**
| Estilo | Característica | Resultado |
|--------|---------------|-----------|
| Pasivo | "Lo que tú digas" | Resentimiento acumulado |
| Agresivo | "Se hace como yo digo" | Conflicto, alejamiento |
| Asertivo | "Necesito X, ¿podemos?" | Respeto mutuo |

**Fórmula DESC**
Para situaciones difíciles:
1. **D**escribe la situación (hechos, sin interpretación)
2. **E**xpresa cómo te sientes (yo me siento... no "tú me haces sentir")
3. **S**ugiere un cambio específico
4. **C**onsecuencias positivas si cambia

**Ejemplo**
"Cuando llegamos tarde a las reuniones (D), yo me siento frustrado/a porque siento que mi tiempo no se valora (E). Me gustaría que empezáramos 5 min antes de la hora acordada (S). Creo que todos podríamos terminar antes y el ambiente sería más relajado (C)."

**La ansiedad social**
La dificultad para ser asertivo/a frecuentemente tiene raíces en el miedo al rechazo o al conflicto. La exposición gradual y la práctica en contextos seguros ayudan a desarrollar esta habilidad.
''',
  ),
];

// ─── Main Screen ──────────────────────────────────────────────────────────────
class LibraryScreen extends StatefulWidget {
  const LibraryScreen({super.key});

  @override
  State<LibraryScreen> createState() => _LibraryScreenState();
}

class _LibraryScreenState extends State<LibraryScreen> {
  String _selectedCategory = 'Todos';

  static final _categories = ['Todos', ...{
    for (final a in _kArticles) a.category
  }];

  List<_Article> get _filtered => _selectedCategory == 'Todos'
      ? _kArticles
      : _kArticles.where((a) => a.category == _selectedCategory).toList();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Biblioteca')),
      body: WebFrame(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Psicoeducación',
                      style: TextStyle(
                          fontSize: 22, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text('${_kArticles.length} artículos · respaldados por evidencia',
                      style: const TextStyle(
                          color: MindraColors.textSecondary, fontSize: 13)),
                ],
              ),
            ),

            // Filtros por categoría
            SizedBox(
              height: 38,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                itemCount: _categories.length,
                separatorBuilder: (_, _) => const SizedBox(width: 8),
                itemBuilder: (_, i) {
                  final cat = _categories[i];
                  final selected = cat == _selectedCategory;
                  return ChoiceChip(
                    label: Text(cat),
                    selected: selected,
                    onSelected: (_) => setState(() => _selectedCategory = cat),
                  );
                },
              ),
            ),
            const SizedBox(height: 12),

            // Lista de artículos
            Expanded(
              child: ListView.separated(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 40),
                itemCount: _filtered.length,
                separatorBuilder: (_, _) => const SizedBox(height: 10),
                itemBuilder: (_, i) => _ArticleCard(article: _filtered[i]),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Tarjeta de artículo ──────────────────────────────────────────────────────
class _ArticleCard extends StatelessWidget {
  final _Article article;
  const _ArticleCard({required this.article});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.push(
          context,
          MaterialPageRoute(
              builder: (_) => _ArticleReader(article: article))),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: MindraColors.darkSurface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
              color: article.color.withValues(alpha: 0.2), width: 1),
        ),
        child: Row(children: [
          Container(
            width: 48, height: 48,
            decoration: BoxDecoration(
              color: article.color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(12),
            ),
            alignment: Alignment.center,
            child: Text(article.emoji, style: const TextStyle(fontSize: 22)),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: article.color.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(99),
                ),
                child: Text(article.category,
                    style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                        color: article.color)),
              ),
              const SizedBox(height: 6),
              Text(article.title,
                  style: const TextStyle(
                      fontSize: 15, fontWeight: FontWeight.w600)),
              const SizedBox(height: 4),
              Text(article.summary,
                  style: const TextStyle(
                      fontSize: 12,
                      color: MindraColors.textSecondary),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis),
            ]),
          ),
          const SizedBox(width: 8),
          Icon(Icons.arrow_forward_ios,
              size: 14, color: MindraColors.textSecondary),
        ]),
      ),
    );
  }
}

// ─── Lector de artículo ───────────────────────────────────────────────────────
class _ArticleReader extends StatelessWidget {
  final _Article article;
  const _ArticleReader({required this.article});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: MindraColors.dark,
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 160,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      article.color,
                      article.color.withValues(alpha: 0.7),
                    ],
                  ),
                ),
                alignment: Alignment.center,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 32),
                    Text(article.emoji,
                        style: const TextStyle(fontSize: 48)),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 3),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(99),
                      ),
                      child: Text(article.category,
                          style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w600)),
                    ),
                  ],
                ),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: WebFrame(
              maxWidth: 680,
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(article.title,
                        style: const TextStyle(
                            fontSize: 24, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text(article.summary,
                        style: const TextStyle(
                            color: MindraColors.textSecondary,
                            fontSize: 14,
                            fontStyle: FontStyle.italic)),
                    const Divider(height: 32),
                    _MarkdownBody(text: article.body),
                    const SizedBox(height: 40),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Renderizador simple de Markdown subset (negrita, listas, tablas).
class _MarkdownBody extends StatelessWidget {
  final String text;
  const _MarkdownBody({required this.text});

  @override
  Widget build(BuildContext context) {
    final lines = text.trim().split('\n');
    final widgets = <Widget>[];

    for (final raw in lines) {
      final line = raw.trimRight();
      if (line.isEmpty) {
        widgets.add(const SizedBox(height: 10));
      } else if (line.startsWith('**') && line.endsWith('**') && line.length > 4) {
        widgets.add(Padding(
          padding: const EdgeInsets.only(top: 12, bottom: 4),
          child: Text(
            line.substring(2, line.length - 2),
            style: const TextStyle(
                fontSize: 16, fontWeight: FontWeight.w700),
          ),
        ));
      } else if (line.startsWith('• ') || line.startsWith('× ')) {
        final isNeg = line.startsWith('×');
        widgets.add(Padding(
          padding: const EdgeInsets.symmetric(vertical: 3),
          child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(isNeg ? '✕ ' : '• ',
                style: TextStyle(
                    color: isNeg ? Colors.red : MindraColors.blue,
                    fontSize: 16,
                    fontWeight: FontWeight.bold)),
            Expanded(
                child: _RichLine(text: line.substring(2))),
          ]),
        ));
      } else if (RegExp(r'^\d+\.').hasMatch(line)) {
        widgets.add(Padding(
          padding: const EdgeInsets.symmetric(vertical: 3),
          child: _RichLine(text: line),
        ));
      } else if (line.startsWith('|')) {
        // tabla simple — skip header separators
        if (line.contains('---')) continue;
        final cells = line.split('|').where((s) => s.isNotEmpty).toList();
        widgets.add(Padding(
          padding: const EdgeInsets.symmetric(vertical: 2),
          child: Row(
            children: cells
                .map((c) => Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 5),
                        decoration: BoxDecoration(
                          border: Border.all(
                              color: Colors.white10, width: .5),
                        ),
                        child: _RichLine(
                            text: c.trim(),
                            style: const TextStyle(fontSize: 12)),
                      ),
                    ))
                .toList(),
          ),
        ));
      } else {
        widgets.add(Padding(
          padding: const EdgeInsets.symmetric(vertical: 2),
          child: _RichLine(text: line),
        ));
      }
    }

    return Column(
        crossAxisAlignment: CrossAxisAlignment.start, children: widgets);
  }
}

/// Renderiza **negrita** inline.
class _RichLine extends StatelessWidget {
  final String text;
  final TextStyle? style;
  const _RichLine({required this.text, this.style});

  @override
  Widget build(BuildContext context) {
    final base = style ??
        const TextStyle(fontSize: 14, height: 1.6);
    final spans = <TextSpan>[];
    final parts = text.split('**');
    for (int i = 0; i < parts.length; i++) {
      spans.add(TextSpan(
        text: parts[i],
        style: i.isOdd
            ? base.copyWith(fontWeight: FontWeight.w700)
            : base,
      ));
    }
    return RichText(text: TextSpan(children: spans));
  }
}
