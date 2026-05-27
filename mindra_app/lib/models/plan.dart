class Plan {
  final int id;
  final String slug;
  final String name;
  final String description;
  final int priceCents;
  final String currency;
  final Map<String, dynamic> features;
  final int trialDays;
  final int billingDays;

  const Plan({
    required this.id,
    required this.slug,
    required this.name,
    required this.description,
    required this.priceCents,
    required this.currency,
    required this.features,
    required this.trialDays,
    this.billingDays = 30,
  });

  factory Plan.fromJson(Map<String, dynamic> json) => Plan(
        id:          json['id'] as int,
        slug:        json['slug'] as String,
        name:        json['name'] as String,
        description: json['description'] as String,
        priceCents:  json['price_cents'] as int,
        currency:    json['currency'] as String,
        features:    Map<String, dynamic>.from(json['features'] as Map),
        trialDays:   json['trial_days'] as int,
        billingDays: json['billing_days'] as int? ?? 30,
      );

  bool get isFree => slug == 'free';
  bool get isPro  => slug == 'pro';
  bool get isPlus => slug == 'plus';

  bool hasFeature(String key) => features[key] == true;

  String get formattedPrice {
    if (isFree)  return 'Gratis';
    if (isPlus)  return 'A medida';
    final amount = (priceCents / 100).toStringAsFixed(0);
    return '\$$amount $currency/mes';
  }

  /// Precio anual con 15% de descuento (solo Pro).
  String get formattedAnnualPrice {
    if (isFree)  return 'Gratis';
    if (isPlus)  return 'A medida';
    final annual = ((priceCents / 100) * 12 * 0.85).toStringAsFixed(0);
    return '\$$annual $currency/año';
  }
}
