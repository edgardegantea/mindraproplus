import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:flutter/material.dart';

/// Punto de quiebre: pantallas ≥ este valor se tratan como "anchas" (web/tablet).
const double kBreakpointWide = 700;

/// Ancho máximo del área de contenido principal en pantallas anchas.
const double kContentMaxWidth = 860;

/// Ancho máximo de formularios de auth (login, registro).
const double kAuthFormMaxWidth = 440;

/// Devuelve true si el contexto tiene un ancho ≥ [kBreakpointWide].
bool isWideScreen(BuildContext context) =>
    MediaQuery.sizeOf(context).width >= kBreakpointWide;

/// Centra el [child] y lo limita a [maxWidth].
/// Útil para envolver el cuerpo de cada pantalla en web.
class WebFrame extends StatelessWidget {
  final Widget child;
  final double maxWidth;
  final EdgeInsetsGeometry padding;

  const WebFrame({
    super.key,
    required this.child,
    this.maxWidth = kContentMaxWidth,
    this.padding = EdgeInsets.zero,
  });

  @override
  Widget build(BuildContext context) {
    if (!kIsWeb && !isWideScreen(context)) return child;
    return Align(
      alignment: Alignment.topCenter,
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxWidth),
        child: Padding(padding: padding, child: child),
      ),
    );
  }
}
