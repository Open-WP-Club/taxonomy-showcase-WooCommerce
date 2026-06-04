import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	MediaUploadCheck,
	MediaUpload,
	PanelColorSettings,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ToggleControl,
	TextControl,
	Button,
	Spinner,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const [ taxonomyOptions, setTaxonomyOptions ] = useState( null );

	useEffect( () => {
		apiFetch( { path: '/woo-taxonomy-blocks/v1/taxonomies' } )
			.then( ( data ) => setTaxonomyOptions( data ) )
			.catch( () =>
				setTaxonomyOptions( [
					{ value: 'product_cat', label: __( 'Product Categories', 'woo-taxonomy-blocks' ) },
				] )
			);
	}, [] );

	const {
		taxonomy,
		limit,
		orderby,
		order,
		hideEmpty,
		imageSize,
		minHeight,
		borderRadius,
		overlayColor,
		overlayOpacity,
		textColor,
		showCount,
		showDescription,
		buttonText,
		buttonStyle,
		showProducts,
		productsPerTerm,
		placeholderImageId,
		placeholderColor,
		excludeTermIds,
	} = attributes;

	const placeholderImageUrl = useSelect(
		( select ) => {
			if ( ! placeholderImageId ) return null;
			return select( 'core' ).getMedia( placeholderImageId )?.source_url ?? null;
		},
		[ placeholderImageId ]
	);

	return (
		<div { ...blockProps }>
			<InspectorControls>
				{ /* ── Content ── */ }
				<PanelBody title={ __( 'Content', 'woo-taxonomy-blocks' ) } initialOpen={ true }>
					{ taxonomyOptions === null ? (
						<Spinner />
					) : (
						<SelectControl
							__next40pxDefaultSize
							label={ __( 'Taxonomy', 'woo-taxonomy-blocks' ) }
							value={ taxonomy }
							options={ taxonomyOptions }
							onChange={ ( val ) => setAttributes( { taxonomy: val } ) }
						/>
					) }
					<RangeControl
						__next40pxDefaultSize
						label={ __( 'Number of terms', 'woo-taxonomy-blocks' ) }
						value={ limit }
						min={ 1 }
						max={ 12 }
						onChange={ ( val ) => setAttributes( { limit: val } ) }
					/>
					<SelectControl
						__next40pxDefaultSize
						label={ __( 'Order by', 'woo-taxonomy-blocks' ) }
						value={ orderby }
						options={ [
							{ value: 'count', label: __( 'Product count', 'woo-taxonomy-blocks' ) },
							{ value: 'name',  label: __( 'Name', 'woo-taxonomy-blocks' ) },
							{ value: 'id',    label: __( 'ID', 'woo-taxonomy-blocks' ) },
							{ value: 'slug',  label: __( 'Slug', 'woo-taxonomy-blocks' ) },
						] }
						onChange={ ( val ) => setAttributes( { orderby: val } ) }
					/>
					<SelectControl
						__next40pxDefaultSize
						label={ __( 'Order', 'woo-taxonomy-blocks' ) }
						value={ order }
						options={ [
							{ value: 'DESC', label: __( 'Descending', 'woo-taxonomy-blocks' ) },
							{ value: 'ASC',  label: __( 'Ascending', 'woo-taxonomy-blocks' ) },
						] }
						onChange={ ( val ) => setAttributes( { order: val } ) }
					/>
					<ToggleControl
						label={ __( 'Hide empty terms', 'woo-taxonomy-blocks' ) }
						checked={ hideEmpty }
						onChange={ ( val ) => setAttributes( { hideEmpty: val } ) }
					/>
					<TextControl
						__next40pxDefaultSize
						label={ __( 'Exclude term IDs', 'woo-taxonomy-blocks' ) }
						help={ __( 'Comma-separated term IDs to exclude.', 'woo-taxonomy-blocks' ) }
						value={ excludeTermIds.join( ', ' ) }
						onChange={ ( val ) =>
							setAttributes( {
								excludeTermIds: val
									.split( ',' )
									.map( ( s ) => parseInt( s.trim(), 10 ) )
									.filter( ( n ) => Number.isFinite( n ) && n > 0 ),
							} )
						}
					/>
					<ToggleControl
						label={ __( 'Show product count', 'woo-taxonomy-blocks' ) }
						checked={ showCount }
						onChange={ ( val ) => setAttributes( { showCount: val } ) }
					/>
					<ToggleControl
						label={ __( 'Show description', 'woo-taxonomy-blocks' ) }
						checked={ showDescription }
						onChange={ ( val ) => setAttributes( { showDescription: val } ) }
					/>
				</PanelBody>

				{ /* ── Layout ── */ }
				<PanelBody title={ __( 'Layout', 'woo-taxonomy-blocks' ) } initialOpen={ false }>
					<RangeControl
						__next40pxDefaultSize
						label={ __( 'Minimum height (px)', 'woo-taxonomy-blocks' ) }
						value={ minHeight }
						min={ 200 }
						max={ 800 }
						step={ 20 }
						onChange={ ( val ) => setAttributes( { minHeight: val } ) }
					/>
					<RangeControl
						__next40pxDefaultSize
						label={ __( 'Border radius (px)', 'woo-taxonomy-blocks' ) }
						value={ borderRadius }
						min={ 0 }
						max={ 32 }
						onChange={ ( val ) => setAttributes( { borderRadius: val } ) }
					/>
					<SelectControl
						__next40pxDefaultSize
						label={ __( 'Image size', 'woo-taxonomy-blocks' ) }
						value={ imageSize }
						options={ [
							{ value: 'medium', label: __( 'Medium', 'woo-taxonomy-blocks' ) },
							{ value: 'large',  label: __( 'Large', 'woo-taxonomy-blocks' ) },
							{ value: 'full',   label: __( 'Full', 'woo-taxonomy-blocks' ) },
						] }
						onChange={ ( val ) => setAttributes( { imageSize: val } ) }
					/>
				</PanelBody>

				{ /* ── Overlay ── */ }
				<PanelColorSettings
					title={ __( 'Overlay', 'woo-taxonomy-blocks' ) }
					initialOpen={ false }
					colorSettings={ [
						{
							value:    overlayColor,
							onChange: ( val ) => setAttributes( { overlayColor: val ?? '#000000' } ),
							label:    __( 'Overlay color', 'woo-taxonomy-blocks' ),
						},
						{
							value:    textColor,
							onChange: ( val ) => setAttributes( { textColor: val ?? '#ffffff' } ),
							label:    __( 'Text color', 'woo-taxonomy-blocks' ),
						},
					] }
				>
					<RangeControl
						__next40pxDefaultSize
						label={ __( 'Overlay opacity (%)', 'woo-taxonomy-blocks' ) }
						value={ overlayOpacity }
						min={ 0 }
						max={ 100 }
						onChange={ ( val ) => setAttributes( { overlayOpacity: val } ) }
					/>
				</PanelColorSettings>

				{ /* ── Button ── */ }
				<PanelBody title={ __( 'Button', 'woo-taxonomy-blocks' ) } initialOpen={ false }>
					<TextControl
						__next40pxDefaultSize
						label={ __( 'Button text', 'woo-taxonomy-blocks' ) }
						value={ buttonText }
						onChange={ ( val ) => setAttributes( { buttonText: val } ) }
					/>
					<SelectControl
						__next40pxDefaultSize
						label={ __( 'Button style', 'woo-taxonomy-blocks' ) }
						value={ buttonStyle }
						options={ [
							{ value: 'outline', label: __( 'Outline', 'woo-taxonomy-blocks' ) },
							{ value: 'filled',  label: __( 'Filled', 'woo-taxonomy-blocks' ) },
							{ value: 'text',    label: __( 'Text only', 'woo-taxonomy-blocks' ) },
						] }
						onChange={ ( val ) => setAttributes( { buttonStyle: val } ) }
					/>
				</PanelBody>

				{ /* ── Featured products ── */ }
				<PanelBody title={ __( 'Featured products', 'woo-taxonomy-blocks' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show featured products', 'woo-taxonomy-blocks' ) }
						help={ __( 'Displays a product strip below each term panel.', 'woo-taxonomy-blocks' ) }
						checked={ showProducts }
						onChange={ ( val ) => setAttributes( { showProducts: val } ) }
					/>
					{ showProducts && (
						<RangeControl
							__next40pxDefaultSize
							label={ __( 'Products per term', 'woo-taxonomy-blocks' ) }
							value={ productsPerTerm }
							min={ 2 }
							max={ 8 }
							onChange={ ( val ) => setAttributes( { productsPerTerm: val } ) }
						/>
					) }
				</PanelBody>

				{ /* ── Placeholder ── */ }
				<PanelBody title={ __( 'Placeholder', 'woo-taxonomy-blocks' ) } initialOpen={ false }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => setAttributes( { placeholderImageId: media.id } ) }
							allowedTypes={ [ 'image' ] }
							value={ placeholderImageId }
							render={ ( { open } ) => (
								<>
									{ placeholderImageUrl && (
										<img
											src={ placeholderImageUrl }
											alt=""
											style={ {
												width: '100%',
												marginBottom: '8px',
												borderRadius: '4px',
												display: 'block',
											} }
										/>
									) }
									<Button
										variant="secondary"
										onClick={ open }
										style={ { width: '100%', justifyContent: 'center', marginBottom: '4px' } }
									>
										{ placeholderImageId
											? __( 'Change placeholder image', 'woo-taxonomy-blocks' )
											: __( 'Upload placeholder image', 'woo-taxonomy-blocks' ) }
									</Button>
									{ placeholderImageId && (
										<Button
											variant="link"
											isDestructive
											onClick={ () => setAttributes( { placeholderImageId: 0 } ) }
										>
											{ __( 'Remove', 'woo-taxonomy-blocks' ) }
										</Button>
									) }
								</>
							) }
						/>
					</MediaUploadCheck>
				</PanelBody>

				<PanelColorSettings
					title={ __( 'Placeholder color', 'woo-taxonomy-blocks' ) }
					initialOpen={ false }
					colorSettings={ [
						{
							value:    placeholderColor,
							onChange: ( val ) => setAttributes( { placeholderColor: val ?? '#cccccc' } ),
							label:    __( 'Background color', 'woo-taxonomy-blocks' ),
						},
					] }
				/>
			</InspectorControls>

			<ServerSideRender
				block="woo-taxonomy-blocks/taxonomy-showcase"
				attributes={ attributes }
				EmptyResponsePlaceholder={ () => (
					<p style={ { padding: '2rem', textAlign: 'center', color: '#666', border: '1px dashed #ccc' } }>
						{ __( 'No terms found. Select a taxonomy or adjust your settings.', 'woo-taxonomy-blocks' ) }
					</p>
				) }
			/>
		</div>
	);
}
