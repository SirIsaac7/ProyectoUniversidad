let zonasGeoJsonLaPaz = null;
let zonasGeoJsonLaPazPromise = null;

function cargarZonasGeoJsonLaPaz() {
  if (zonasGeoJsonLaPaz) {
    return Promise.resolve(zonasGeoJsonLaPaz);
  }

  if (!zonasGeoJsonLaPazPromise) {
    zonasGeoJsonLaPazPromise = fetch('/assets/js/maps/LaPaz/zonasGAMPL.geojson')
      .then(function (response) {
        if (!response.ok) {
          throw new Error('No se pudo cargar el GeoJSON de zonas de La Paz.');
        }

        return response.json();
      })
      .then(function (geojson) {
        zonasGeoJsonLaPaz = geojson;
        return zonasGeoJsonLaPaz;
      })
      .catch(function () {
        zonasGeoJsonLaPaz = { features: [] };
        return zonasGeoJsonLaPaz;
      });
  }

  return zonasGeoJsonLaPazPromise;
}

function iniciarMapasLaPaz() {
  if (typeof jsVectorMap === 'undefined') {
    return;
  }

  cargarZonasGeoJsonLaPaz().then(function (geojson) {
    const zonasPorNombre = indexarZonasGeoJsonLaPaz(geojson);

    document.querySelectorAll('[data-mapa-lapaz]').forEach(function (mapElement) {
      if (mapElement.dataset.mapaInicializado === 'true' || mapElement.offsetParent === null) {
        return;
      }

      mapElement.dataset.mapaInicializado = 'true';

      const zonaProveedor = normalizarZonaMapaLaPaz(mapElement.dataset.zona || '');
      const mapaEstatico = mapElement.dataset.mapaEstatico === 'true';

      const mapa = new jsVectorMap({
        selector: '#' + mapElement.id,
        map: 'municipio_lapaz',
        backgroundColor: 'transparent',
        zoomButtons: !mapaEstatico,
        zoomOnScroll: false,
        draggable: !mapaEstatico,
        regionsSelectable: false,
        regionsSelectableOne: false,

        regionStyle: {
          initial: {
            fill: '#eef2ff',
            stroke: '#c7d2fe',
            strokeWidth: 0.8
          },
          hover: {
            fill: '#8b5cf6'
          },
          selected: {
            fill: '#6d28d9',
            stroke: '#4c1d95',
            strokeWidth: 1.6
          },
          selectedHover: {
            fill: '#7c3aed'
          }
        },

        onRegionTooltipShow(event, tooltip, code) {
          const region = this._mapData.paths[code];
          const zona = zonasPorNombre.get(normalizarZonaMapaLaPaz(region?.name || code));

          if (zona) {
            tooltip.text(
              '<strong>' + zona.zona + '</strong><br>' +
              'Macrodistrito: ' + zona.macrodistr + '<br>' +
              'Distrito: ' + zona.distrito,
              true
            );
            return;
          }

          tooltip.text(region?.name || code);
        }
      });

      const selectedRegions = buscarRegionesLaPaz(mapa._mapData, zonaProveedor);

      if (selectedRegions.length && typeof mapa.setSelectedRegions === 'function') {
        mapa.setSelectedRegions(selectedRegions);
      }

      registrarTitulosMapaLaPaz(mapElement, mapa, zonasPorNombre);
      aplicarResaltadoMapaLaPaz(mapElement, mapa, selectedRegions);

      window.dispatchEvent(new CustomEvent('mapa-lapaz:inicializado', {
        detail: {
          id: mapElement.id
        }
      }));
    });
  });
}

function buscarRegionesLaPaz(mapaLaPaz, zonaProveedor) {
  if (!zonaProveedor) {
    return [];
  }

  const paths = Object.entries(mapaLaPaz?.paths || {});
  const exactas = paths
    .filter(function ([, region]) {
      return normalizarZonaMapaLaPaz(region.name || '') === zonaProveedor;
    })
    .map(function ([code]) {
      return code;
    });

  if (exactas.length) {
    return exactas;
  }

  return paths
    .filter(function ([, region]) {
      return normalizarZonaMapaLaPaz(region.name || '').includes(zonaProveedor);
    })
    .map(function ([code]) {
      return code;
    });
}

function aplicarResaltadoMapaLaPaz(mapElement, mapa, selectedRegions) {
  if (!selectedRegions.length) {
    return;
  }

  selectedRegions.forEach(function (code) {
    const region = mapa.regions?.[code]?.element;

    if (!region) {
      return;
    }

    region.select(true);
    region.setStyle({
      fill: '#6d28d9',
      stroke: '#4c1d95',
      strokeWidth: 1.6
    });
  });

  window.setTimeout(function () {
    selectedRegions.forEach(function (code) {
      const region = mapElement.querySelector('[data-code="' + code + '"]');

      if (!region) {
        return;
      }

      region.classList.add('is-zona-proveedor');
      region.style.fill = '#6d28d9';
      region.style.stroke = '#4c1d95';
      region.style.strokeWidth = '1.6';
    });
  }, 80);
}

function registrarTitulosMapaLaPaz(mapElement, mapa, zonasPorNombre) {
  Object.entries(mapa._mapData?.paths || {}).forEach(function ([code, regionData]) {
    const region = mapElement.querySelector('[data-code="' + code + '"]');

    if (!region || region.querySelector('title')) {
      return;
    }

    const zona = zonasPorNombre.get(normalizarZonaMapaLaPaz(regionData.name || code));
    const titulo = document.createElementNS('http://www.w3.org/2000/svg', 'title');

    titulo.textContent = zona
      ? zona.zona + ' - ' + zona.macrodistr + ' / Distrito ' + zona.distrito
      : regionData.name || code;

    region.appendChild(titulo);
  });
}

function indexarZonasGeoJsonLaPaz(geojson) {
  const zonas = new Map();

  (geojson?.features || []).forEach(function (feature) {
    const properties = feature.properties || {};
    const nombreZona = properties.zona || properties.zonaref;

    if (!nombreZona) {
      return;
    }

    zonas.set(normalizarZonaMapaLaPaz(nombreZona), {
      zona: properties.zona || nombreZona,
      macrodistr: properties.macrodistr || properties.subalcaldi || 'Sin macrodistrito',
      distrito: properties.distrito || 'Sin distrito',
      codigozona: properties.codigozona || null
    });
  });

  return zonas;
}

function normalizarTextoMapaLaPaz(texto) {
  return texto
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

function normalizarZonaMapaLaPaz(texto) {
  return normalizarTextoMapaLaPaz(texto)
    .split(',')[0]
    .replace(/^(zona|barrio)\s+/g, '')
    .trim();
}

window.iniciarMapasLaPaz = iniciarMapasLaPaz;

document.addEventListener('DOMContentLoaded', iniciarMapasLaPaz);
document.addEventListener('shown.bs.modal', iniciarMapasLaPaz);
document.addEventListener('shown.bs.tab', iniciarMapasLaPaz);
